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

use core\hook\output\before_html_attributes;
use core\hook\output\before_http_headers;
use local_nevertranslate\local\techniques;

/**
 * Tests that the hook callbacks are registered and reach the rendered page.
 *
 * @package    local_nevertranslate
 * @category   test
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_nevertranslate\hook_callbacks::class)]
final class hook_callbacks_test extends \advanced_testcase {
    /**
     * The html element gets translate="no" and the class, merged with an existing one.
     */
    public function test_html_attributes(): void {
        global $PAGE;
        $this->resetAfterTest();
        unset_config(techniques::CONFIG, 'local_nevertranslate');

        $hook = new before_html_attributes($PAGE->get_renderer('core'), ['class' => 'fromanotherplugin']);
        \core\di::get(\core\hook\manager::class)->dispatch($hook);
        $attributes = $hook->get_attributes();
        $this->assertSame('no', $attributes['translate']);
        $this->assertSame('fromanotherplugin notranslate', $attributes['class']);

        // And through the real renderer method the themes call.
        $this->assertMatchesRegularExpression('/ translate="no"/', $PAGE->get_renderer('core')->htmlattributes());
        $this->assertMatchesRegularExpression('/ class="notranslate"/', $PAGE->get_renderer('core')->htmlattributes());
    }

    /**
     * The meta tag and guard script reach the standard head HTML.
     */
    public function test_standard_head_html(): void {
        global $PAGE;
        $this->resetAfterTest();
        unset_config(techniques::CONFIG, 'local_nevertranslate');
        $PAGE->set_url('/index.php');

        $head = $PAGE->get_renderer('core')->standard_head_html();
        $this->assertStringContainsString('<meta name="google" content="notranslate" />', $head);
        $this->assertStringContainsString('<meta name="robots" content="notranslate" />', $head);
        $this->assertStringContainsString('local_nevertranslate_reloaded', $head);
    }

    /**
     * The body script reaches the top of the body.
     */
    public function test_standard_top_of_body_html(): void {
        global $PAGE;
        $this->resetAfterTest();
        unset_config(techniques::CONFIG, 'local_nevertranslate');
        $PAGE->set_url('/index.php');

        $html = $PAGE->get_renderer('core')->standard_top_of_body_html();
        $this->assertStringContainsString("document.body.setAttribute('translate', 'no');", $html);
    }

    /**
     * The body class is added while it still can be.
     */
    public function test_body_class(): void {
        global $PAGE;
        $this->resetAfterTest();
        unset_config(techniques::CONFIG, 'local_nevertranslate');

        \core\di::get(\core\hook\manager::class)->dispatch(new before_http_headers($PAGE->get_renderer('core')));
        $this->assertStringContainsString('notranslate', $PAGE->bodyclasses);
    }

    /**
     * With every technique switched off, nothing is injected.
     */
    public function test_all_disabled(): void {
        global $PAGE;
        $this->resetAfterTest();
        set_config(techniques::CONFIG, '', 'local_nevertranslate');
        $PAGE->set_url('/index.php');
        $renderer = $PAGE->get_renderer('core');

        $this->assertStringNotContainsString('translate=', $renderer->htmlattributes());
        $this->assertStringNotContainsString('notranslate', $renderer->standard_head_html());
        $this->assertStringNotContainsString("setAttribute('translate'", $renderer->standard_top_of_body_html());
        \core\di::get(\core\hook\manager::class)->dispatch(new before_http_headers($renderer));
        $this->assertStringNotContainsString('notranslate', $PAGE->bodyclasses);
    }
}
