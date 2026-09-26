# Never translate (local_nevertranslate)

A Moodle local plugin that stops browsers, browser extensions and translation
services from automatically translating a Moodle site. It injects every
page-level technique those tools are known to honour into every page of the
site. An administrator can switch each technique on or off.

Requires Moodle 5.2 (`$plugin->supported = [502, 502]`). The CI workflow in
`.github/workflows/ci.yml` tests MOODLE_502_STABLE on PHP 8.3 and 8.4, PostgreSQL and MariaDB.

## Installation

1. Copy the plugin to `public/local/nevertranslate` in your Moodle code.
2. Run `php admin/cli/upgrade.php` (or visit Site administration > Notifications).
3. Configure it at Site administration > Plugins > Local plugins > Never translate.

After installing, purge caches if the markup doesn't appear: Moodle caches the list
of hook callbacks.

## Techniques

All are on by default except *Revert*. The settings page has a "Select all/none" toggle.

| Technique | What it emits | Who honours it |
|---|---|---|
| `htmltranslate` | `<html translate="no">` | The HTML standard's attribute. Google Translate (Chrome's built-in translation and the website widget) and Yandex leave the whole page untranslated, including titles and placeholders. Stops Chrome on iOS from offering to translate. It does **not** stop Chrome on desktop or Android from offering. |
| `htmlclass` | `<html class="notranslate">` | Google Translate, the Microsoft Translator service, DeepL, Immersive Translate. It's merged with any class other plugins add. |
| `metagoogle` | `<meta name="google" content="notranslate">` | **The only markup that stops Chrome on desktop and Android from offering to translate or translating automatically.** Reportedly also stops Microsoft Edge's offer. |
| `bodyclass` | `<body class="notranslate">` | Firefox's built-in Translations looks for markers on `<body>`, not on `<html>`. Also Google, DeepL, Immersive Translate. |
| `bodytranslate` | `<body translate="no">`, set by a one-line script right after `<body>` | Same as above. Moodle has no hook for body attributes, only body classes. |
| `metarobots` | `<meta name="robots" content="notranslate">` | Google Search only: search results don't offer a translated version of the page. |
| `xrobotstag` | `X-Robots-Tag: notranslate` header | Google Search only. |
| `guard` | Inline head script | Puts the markers above back if something removes them. Changes any `translate="yes"` (or empty `translate`) in page content, for example in HTML pasted into the editor, to `translate="no"`. |
| `revert` | Inline head script (off by default) | When the page has been translated (detected from Google's `translated-ltr`/`translated-rtl` class, a changed `lang`, or Edge's `_msttexthash` markers), it clears the Google widget's `googtrans` cookie and reloads the page. If the translation comes straight back (within 30 seconds, for example because the browser always translates), it gives up for that page view. It never reloads a form submission (POST). |

### What no markup can do

- **A user who deliberately chooses "Translate" from the browser menu can still do
  so.** Chrome, Edge and Firefox all allow this. The markers above keep the content
  untranslated in most translators, but they can't hide the menu item. *Revert* undoes
  such a translation by reloading. That overrides the user's choice and loses anything
  typed into the page, which is why it is opt-in.
- To switch translation off in the browsers themselves, use browser policies, for
  example `TranslateEnabled` for Chrome and Edge.
- Pages Moodle outputs without its theme aren't covered: the static CLI maintenance
  page, very early error pages, and AJAX or web service responses.

### Deliberately not included

These were researched and left out because no evidence was found that anything
honours them, or because they're harmful: `<meta name="translate" content="no">`,
`<meta name="microsoft" content="notranslate">`, `style="notranslate"`,
`<meta http-equiv="Content-Language">`, Bing-specific robots rules, and the
`skiptranslate` class, which is also the Google widget's own UI class and is
commonly hidden by CSS. Moodle core already sends `Content-Language` and sets
`lang` on `<html>`. Those help language detection but aren't an opt-out.

## Privacy

The plugin stores no personal data. The *Revert* script keeps a timestamp per page in
the browser's `sessionStorage` (keys starting `local_nevertranslate_reloaded:`) to prevent reload loops.

## License

GNU GPL v3 or later. See `LICENSE`.
