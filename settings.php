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
 * Admin settings for local_nevertranslate.
 *
 * @package    local_nevertranslate
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_nevertranslate', new lang_string('pluginname', 'local_nevertranslate'));

    $choices = [];
    $defaults = [];
    foreach (\local_nevertranslate\local\techniques::defaults() as $key => $enabled) {
        $choices[$key] = new lang_string('technique_' . $key, 'local_nevertranslate');
        if ($enabled) {
            $defaults[$key] = 1;
        }
    }

    $settings->add(new \local_nevertranslate\admin\setting_techniques(
        'local_nevertranslate/' . \local_nevertranslate\local\techniques::CONFIG,
        new lang_string('techniques', 'local_nevertranslate'),
        new lang_string('techniques_desc', 'local_nevertranslate'),
        $defaults,
        $choices
    ));

    $ADMIN->add('localplugins', $settings);
}
