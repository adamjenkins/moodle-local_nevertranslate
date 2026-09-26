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
 * Adds a tri-state "Select all/none" checkbox above a multi-checkbox admin setting.
 *
 * @module     local_nevertranslate/selectall
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialise the toggle for one setting.
 *
 * @param {string} settingid The setting's id; its checkboxes have the ids <settingid>_<key>.
 * @param {string} label The toggle's label.
 */
export const init = (settingid, label) => {
    const boxes = [...document.querySelectorAll('input[type="checkbox"][id^="' + settingid + '_"]')];
    if (!boxes.length) {
        return;
    }
    const list = boxes[0].closest('ul');
    const toggleid = settingid + '__selectallnone';
    if (!list || document.getElementById(toggleid)) {
        return;
    }

    const toggle = document.createElement('input');
    toggle.type = 'checkbox';
    toggle.id = toggleid;
    toggle.setAttribute('aria-controls', boxes.map((box) => box.id).join(' '));

    // A <strong> rather than a Bootstrap utility class: the bold class differs between
    // Bootstrap 4 (Moodle 4.5) and 5 (Moodle 5.x), and the other one is flagged as deprecated.
    const toggletext = document.createElement('label');
    toggletext.htmlFor = toggleid;
    const strong = document.createElement('strong');
    strong.textContent = label;
    toggletext.append(strong);

    const wrapper = document.createElement('div');
    wrapper.className = 'local_nevertranslate-selectallnone mb-1';
    wrapper.append(toggle, ' ', toggletext);
    list.before(wrapper);

    const refresh = () => {
        const checked = boxes.filter((box) => box.checked).length;
        toggle.checked = checked === boxes.length;
        toggle.indeterminate = checked > 0 && checked < boxes.length;
    };

    toggle.addEventListener('change', () => {
        boxes.forEach((box) => {
            if (box.checked !== toggle.checked) {
                box.checked = toggle.checked;
                // Let Moodle's form change checker notice the change.
                box.dispatchEvent(new Event('change', {bubbles: true}));
            }
        });
        refresh();
    });
    boxes.forEach((box) => box.addEventListener('change', refresh));
    refresh();
};
