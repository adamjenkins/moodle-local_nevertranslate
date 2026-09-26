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

namespace local_nevertranslate\admin;

/**
 * Multi-checkbox admin setting with a "Select all/none" toggle.
 *
 * The toggle is added client-side (amd/src/selectall.js) when there are more
 * than TOGGLE_THRESHOLD choices; without JavaScript the plain checkbox list
 * still works.
 *
 * @package    local_nevertranslate
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_techniques extends \admin_setting_configmulticheckbox {
    /** @var int The toggle is shown when there are more choices than this. */
    public const TOGGLE_THRESHOLD = 5;

    /**
     * Render the setting, and load the select all/none toggle if needed.
     *
     * @param mixed $data array or string depending on setting
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $PAGE;

        if ($this->load_choices() && count($this->choices) > self::TOGGLE_THRESHOLD && !$this->is_readonly()) {
            $PAGE->requires->js_call_amd('local_nevertranslate/selectall', 'init', [
                $this->get_id(),
                get_string('selectallnone', 'local_nevertranslate'),
            ]);
        }
        return parent::output_html($data, $query);
    }
}
