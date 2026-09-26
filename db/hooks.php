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

/**
 * Hook callbacks for local_nevertranslate.
 *
 * @package    local_nevertranslate
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\output\before_http_headers::class,
        'callback' => [\local_nevertranslate\hook_callbacks::class, 'before_http_headers'],
    ],
    [
        'hook' => \core\hook\output\before_html_attributes::class,
        'callback' => [\local_nevertranslate\hook_callbacks::class, 'before_html_attributes'],
        // Callbacks run highest priority first: run late so that the class
        // attribute is merged with, not overwritten by, other plugins' classes.
        'priority' => 0,
    ],
    [
        'hook' => \core\hook\output\before_standard_head_html_generation::class,
        'callback' => [\local_nevertranslate\hook_callbacks::class, 'before_standard_head_html_generation'],
    ],
    [
        'hook' => \core\hook\output\before_standard_top_of_body_html_generation::class,
        'callback' => [\local_nevertranslate\hook_callbacks::class, 'before_standard_top_of_body_html_generation'],
    ],
];
