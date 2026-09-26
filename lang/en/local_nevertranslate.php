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
 * English language strings for local_nevertranslate.
 *
 * @package    local_nevertranslate
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// The technique_* strings are checkbox labels: core escapes them in the "Default" line and
// highlights search matches inside them, so they must stay plain text (no <, > or &).
$string['pluginname'] = 'Never translate';
$string['privacy:metadata'] = 'The Never translate plugin does not store any personal data.';
$string['selectallnone'] = 'Select all/none';
$string['technique_bodyclass'] = 'Body class: class="notranslate" on the body element';
$string['technique_bodytranslate'] = 'Body attribute: translate="no" on the body element';
$string['technique_guard'] = 'Guard script: keep the markers in place and neutralise translate="yes" in page content';
$string['technique_htmlclass'] = 'Page class: class="notranslate" on the html element';
$string['technique_htmltranslate'] = 'Page attribute: translate="no" on the html element';
$string['technique_metagoogle'] = 'Google meta tag: name="google" content="notranslate"';
$string['technique_metarobots'] = 'Robots meta tag: name="robots" content="notranslate" (Google Search)';
$string['technique_revert'] = 'Revert script: reload a translated page (overrides the user\'s choice)';
$string['technique_xrobotstag'] = 'HTTP header: X-Robots-Tag: notranslate (Google Search)';
$string['techniques'] = 'Techniques';
$string['techniques_desc'] = '<p>Choose which techniques are used, on every page of the site, to stop browsers and translation services from translating it automatically.</p>
<ul>
<li><strong>Page attribute</strong>: <code>translate="no"</code> is the HTML standard\'s attribute. Google Translate (Chrome\'s built-in translation and the website widget) and Yandex leave the whole page untranslated, including titles and placeholders. It stops Chrome on iOS from offering to translate.</li>
<li><strong>Page class</strong>: <code>class="notranslate"</code> is honoured by Google Translate, the Microsoft Translator service, DeepL and Immersive Translate. It is merged with any classes other plugins add.</li>
<li><strong>Google meta tag</strong>: the only markup that stops Chrome on desktop and Android from offering to translate the page or translating it automatically. Reportedly also stops Microsoft Edge\'s offer.</li>
<li><strong>Body class</strong> and <strong>body attribute</strong>: Firefox looks for these markers on the body, not on the html element. The attribute is set by a one-line script, because Moodle has no hook for body attributes.</li>
<li><strong>Robots meta tag</strong> and <strong>HTTP header</strong>: Google Search only. Search results do not offer a translated version of the page. No effect on browsers.</li>
<li><strong>Guard script</strong>: puts the markers above back if something removes them, and changes any <code>translate="yes"</code> in page content (for example in HTML pasted into the editor) to <code>translate="no"</code>, because that would otherwise re-enable translation for that part of the page.</li>
<li><strong>Revert script</strong>: when the page has been translated (by Chrome, Edge, Firefox or the Google Translate widget), it clears the widget\'s <code>googtrans</code> cookie and reloads the page. If the translation comes straight back, it gives up for that page. It never reloads the result of a form submission. It overrides a translation the user chose, and a reload loses anything typed into the page, so it is off by default.</li>
</ul>
<p>No markup can stop a user who deliberately chooses to translate from the browser menu, but in most translators the markers still keep the content itself untranslated. Pages that Moodle outputs without its theme (the static CLI maintenance page, very early error pages, and AJAX or web service responses) are not covered. To switch translation off in the browsers themselves, use browser policies such as <code>TranslateEnabled</code> for Chrome and Edge.</p>';
