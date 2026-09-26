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
use core\hook\output\before_standard_head_html_generation;
use core\hook\output\before_standard_top_of_body_html_generation;
use local_nevertranslate\local\techniques;

/**
 * Hook callbacks that inject the enabled anti-translation techniques into every page.
 *
 * @package    local_nevertranslate
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Should the callbacks do anything at all?
     *
     * Hook callbacks are registered for every plugin on disk, installed or not.
     *
     * @return bool
     */
    protected static function active(): bool {
        return !during_initial_install();
    }

    /**
     * Send the HTTP headers and add the body class.
     *
     * This is the last hook that runs while the page is still in
     * STATE_BEFORE_HEADER, after which add_body_class() throws.
     *
     * @param before_http_headers $hook
     */
    public static function before_http_headers(before_http_headers $hook): void {
        if (!self::active()) {
            return;
        }
        if (!headers_sent()) {
            foreach (techniques::http_headers() as $header) {
                // Do not replace an X-Robots-Tag header another component may have sent.
                header($header, false);
            }
        }
        $page = $hook->renderer->get_page();
        foreach (techniques::body_classes() as $class) {
            $page->add_body_class($class);
        }
    }

    /**
     * Add attributes to the html element.
     *
     * @param before_html_attributes $hook
     */
    public static function before_html_attributes(before_html_attributes $hook): void {
        if (!self::active()) {
            return;
        }
        foreach (techniques::html_attributes($hook->get_attributes()) as $name => $value) {
            $hook->add_attribute($name, $value);
        }
    }

    /**
     * Add the meta tags and the guard script to the head.
     *
     * @param before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(before_standard_head_html_generation $hook): void {
        if (!self::active()) {
            return;
        }
        $hook->add_html(techniques::head_html());
    }

    /**
     * Mark the body element as not translatable, as soon as it exists.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(
        before_standard_top_of_body_html_generation $hook
    ): void {
        if (!self::active()) {
            return;
        }
        $hook->add_html(techniques::top_of_body_html());
    }
}
