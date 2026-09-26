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

namespace local_nevertranslate\local;

/**
 * Registry of the anti-translation techniques and the markup each one emits.
 *
 * Every technique is independently switchable from the admin settings page
 * (config key local_nevertranslate/techniques, a comma-separated list of the
 * enabled technique keys).
 *
 * @package    local_nevertranslate
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class techniques {
    /** @var string translate="no" on the html element. */
    public const HTML_TRANSLATE = 'htmltranslate';

    /** @var string class="notranslate" on the html element. */
    public const HTML_CLASS = 'htmlclass';

    /** @var string meta name="google" content="notranslate" in the head. */
    public const META_GOOGLE = 'metagoogle';

    /** @var string class="notranslate" on the body element. */
    public const BODY_CLASS = 'bodyclass';

    /** @var string translate="no" on the body element (set by an inline script). */
    public const BODY_TRANSLATE = 'bodytranslate';

    /** @var string meta name="robots" content="notranslate" in the head. */
    public const META_ROBOTS = 'metarobots';

    /** @var string X-Robots-Tag: notranslate HTTP response header. */
    public const X_ROBOTS_TAG = 'xrobotstag';

    /** @var string Script that keeps the markers in place and neutralises translate="yes" overrides. */
    public const GUARD = 'guard';

    /** @var string Script that reloads the page once when it detects a translation. */
    public const REVERT = 'revert';

    /** @var string Config name (within the plugin) holding the enabled technique keys. */
    public const CONFIG = 'techniques';

    /** @var string sessionStorage key the revert script uses to stop reload loops. */
    public const RELOAD_KEY = 'local_nevertranslate_reloaded';

    /**
     * All technique keys with their default enabled state, in display order.
     *
     * @return array technique key => bool enabled by default
     */
    public static function defaults(): array {
        return [
            self::HTML_TRANSLATE => true,
            self::HTML_CLASS => true,
            self::META_GOOGLE => true,
            self::BODY_CLASS => true,
            self::BODY_TRANSLATE => true,
            self::META_ROBOTS => true,
            self::X_ROBOTS_TAG => true,
            self::GUARD => true,
            // Reloading the page overrides an explicit action of the user, so it is opt-in.
            self::REVERT => false,
        ];
    }

    /**
     * Get the enabled technique keys.
     *
     * Before the setting has ever been saved (or defaults applied) the config
     * value is false, in which case the defaults apply.
     *
     * @return string[] enabled technique keys
     */
    public static function enabled(): array {
        $config = get_config('local_nevertranslate', self::CONFIG);
        if ($config === false || $config === null) {
            return array_keys(array_filter(self::defaults()));
        }
        $keys = array_filter(array_map('trim', explode(',', (string) $config)));
        return array_values(array_intersect(array_keys(self::defaults()), $keys));
    }

    /**
     * Is a technique enabled?
     *
     * @param string $key technique key
     * @return bool
     */
    public static function is_enabled(string $key): bool {
        return in_array($key, self::enabled(), true);
    }

    /**
     * Attributes to add to the html element.
     *
     * @param array $existing attributes already on the element (name => value)
     * @return array attribute name => value to set
     */
    public static function html_attributes(array $existing = []): array {
        $attributes = [];
        if (self::is_enabled(self::HTML_TRANSLATE)) {
            $attributes['translate'] = 'no';
        }
        if (self::is_enabled(self::HTML_CLASS)) {
            $attributes['class'] = self::merge_class($existing['class'] ?? '', 'notranslate');
        }
        return $attributes;
    }

    /**
     * Add a class to a space-separated class list, without duplicating it.
     *
     * @param string $classes existing class list
     * @param string $add class to add
     * @return string
     */
    public static function merge_class(string $classes, string $add): string {
        $list = preg_split('/\s+/', trim($classes), -1, PREG_SPLIT_NO_EMPTY);
        if (!in_array($add, $list, true)) {
            $list[] = $add;
        }
        return implode(' ', $list);
    }

    /**
     * HTML to emit inside the head element.
     *
     * The meta tags must be direct children of head: Chromium only looks there.
     *
     * @return string
     */
    public static function head_html(): string {
        $html = '';
        if (self::is_enabled(self::META_GOOGLE)) {
            $html .= \html_writer::empty_tag('meta', ['name' => 'google', 'content' => 'notranslate']) . "\n";
        }
        if (self::is_enabled(self::META_ROBOTS)) {
            $html .= \html_writer::empty_tag('meta', ['name' => 'robots', 'content' => 'notranslate']) . "\n";
        }
        $script = self::guard_script();
        if ($script !== '') {
            $html .= \html_writer::script($script) . "\n";
        }
        return $html;
    }

    /**
     * HTML to emit immediately after the opening body tag.
     *
     * The body element has no attribute hook (only a class list), so its
     * translate attribute is set by a script that runs as soon as the parser
     * reaches it, before any of the page content exists.
     *
     * @return string
     */
    public static function top_of_body_html(): string {
        if (!self::is_enabled(self::BODY_TRANSLATE)) {
            return '';
        }
        return \html_writer::script("document.body.setAttribute('translate', 'no');") . "\n";
    }

    /**
     * Classes to add to the body element.
     *
     * @return string[]
     */
    public static function body_classes(): array {
        return self::is_enabled(self::BODY_CLASS) ? ['notranslate'] : [];
    }

    /**
     * HTTP response headers to send.
     *
     * @return string[] complete header lines
     */
    public static function http_headers(): array {
        return self::is_enabled(self::X_ROBOTS_TAG) ? ['X-Robots-Tag: notranslate'] : [];
    }

    /**
     * The inline guard/revert script for the head, or '' if neither is enabled.
     *
     * It runs inline and early (rather than as an AMD module, which only loads
     * at the end of the page) so that it is watching before a translator starts.
     *
     * @return string JavaScript source
     */
    public static function guard_script(): string {
        $guard = self::is_enabled(self::GUARD);
        $revert = self::is_enabled(self::REVERT);
        if (!$guard && !$revert) {
            return '';
        }
        $options = json_encode([
            'guard' => $guard,
            'revert' => $revert,
            'htmltranslate' => self::is_enabled(self::HTML_TRANSLATE),
            'htmlclass' => self::is_enabled(self::HTML_CLASS),
            'bodytranslate' => self::is_enabled(self::BODY_TRANSLATE),
            'bodyclass' => self::is_enabled(self::BODY_CLASS),
            'reloadkey' => self::RELOAD_KEY,
            'post' => ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST',
        ]);
        $source = file_get_contents(__DIR__ . '/../../js/guard.js');
        return '(' . trim($source) . ')(' . $options . ');';
    }
}
