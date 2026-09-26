<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_nevertranslate;

use local_nevertranslate\local\techniques;

/**
 * Tests for the technique registry.
 *
 * @package    local_nevertranslate
 * @category   test
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_nevertranslate\local\techniques::class)]
final class techniques_test extends \advanced_testcase {
    /**
     * Enable exactly the given techniques.
     *
     * @param string[] $keys technique keys
     */
    private function enable(array $keys): void {
        set_config(techniques::CONFIG, implode(',', $keys), 'local_nevertranslate');
    }

    /**
     * Before the setting is saved, the defaults apply (everything but revert).
     */
    public function test_enabled_defaults_when_unset(): void {
        $this->resetAfterTest();
        unset_config(techniques::CONFIG, 'local_nevertranslate');
        $this->assertFalse(get_config('local_nevertranslate', techniques::CONFIG));

        $enabled = techniques::enabled();
        $this->assertCount(8, $enabled);
        $this->assertContains(techniques::META_GOOGLE, $enabled);
        $this->assertNotContains(techniques::REVERT, $enabled);
    }

    /**
     * A saved empty list means none, and unknown keys are ignored.
     */
    public function test_enabled_saved_values(): void {
        $this->resetAfterTest();
        $this->enable([]);
        $this->assertSame([], techniques::enabled());

        $this->enable(['bogus', techniques::REVERT, techniques::HTML_CLASS]);
        $this->assertSame([techniques::HTML_CLASS, techniques::REVERT], techniques::enabled());
    }

    /**
     * The html class is merged with existing classes, never duplicated.
     */
    public function test_html_attributes(): void {
        $this->resetAfterTest();
        $this->enable([techniques::HTML_TRANSLATE, techniques::HTML_CLASS]);
        $this->assertSame(
            ['translate' => 'no', 'class' => 'other notranslate'],
            techniques::html_attributes(['class' => ' other  '])
        );
        $this->assertSame('a notranslate', techniques::html_attributes(['class' => 'a notranslate'])['class']);

        $this->enable([techniques::HTML_TRANSLATE]);
        $this->assertSame(['translate' => 'no'], techniques::html_attributes(['class' => 'a']));
    }

    /**
     * Class merging keeps the existing order and adds the class once.
     */
    public function test_merge_class(): void {
        $this->assertSame('notranslate', techniques::merge_class('', 'notranslate'));
        $this->assertSame('a b notranslate', techniques::merge_class(' a   b ', 'notranslate'));
        $this->assertSame('notranslate a', techniques::merge_class('notranslate a', 'notranslate'));
    }

    /**
     * Head HTML carries exactly the enabled meta tags and script.
     */
    public function test_head_html(): void {
        $this->resetAfterTest();
        $this->enable([techniques::META_GOOGLE, techniques::META_ROBOTS]);
        $html = techniques::head_html();
        $this->assertStringContainsString('<meta name="google" content="notranslate" />', $html);
        $this->assertStringContainsString('<meta name="robots" content="notranslate" />', $html);
        $this->assertStringNotContainsString('<script', $html);

        $this->enable([techniques::GUARD]);
        $html = techniques::head_html();
        $this->assertStringNotContainsString('<meta', $html);
        $this->assertStringContainsString('<script', $html);

        $this->enable([]);
        $this->assertSame('', techniques::head_html());
    }

    /**
     * The guard script is a self-invoking function carrying the enabled options.
     */
    public function test_guard_script(): void {
        $this->resetAfterTest();
        $this->enable([techniques::REVERT, techniques::BODY_CLASS]);
        $script = techniques::guard_script();
        $this->assertStringStartsWith('(function(options) {', $script);
        $this->assertStringEndsWith(')({"guard":false,"revert":true,"htmltranslate":false,"htmlclass":false,'
            . '"bodytranslate":false,"bodyclass":true,"reloadkey":"local_nevertranslate_reloaded","post":false});', $script);

        // A form submission must never be reloaded (the browser would ask to resubmit it).
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $script = techniques::guard_script();
        if ($method === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $method;
        }
        $this->assertStringEndsWith(',"post":true});', $script);

        $this->enable([techniques::HTML_CLASS]);
        $this->assertSame('', techniques::guard_script());
    }

    /**
     * Body markers and HTTP headers follow their switches.
     */
    public function test_body_and_headers(): void {
        $this->resetAfterTest();
        $this->enable([techniques::BODY_CLASS, techniques::BODY_TRANSLATE, techniques::X_ROBOTS_TAG]);
        $this->assertSame(['notranslate'], techniques::body_classes());
        $this->assertStringContainsString("document.body.setAttribute('translate', 'no');", techniques::top_of_body_html());
        $this->assertSame(['X-Robots-Tag: notranslate'], techniques::http_headers());

        $this->enable([]);
        $this->assertSame([], techniques::body_classes());
        $this->assertSame('', techniques::top_of_body_html());
        $this->assertSame([], techniques::http_headers());
    }
}
