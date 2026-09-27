# Changes

## Unreleased

- Declare Moodle 5.3 support.

## v0.1.0

- First release. Injects, on every themed page of the site, the techniques that
  stop browsers and translation services from translating it automatically:
  `translate="no"` and `class="notranslate"` on `<html>` and `<body>`,
  `<meta name="google" content="notranslate">`, the Google Search `notranslate`
  robots meta tag and `X-Robots-Tag` header, a guard script that keeps the markers
  in place and neutralises `translate="yes"` in page content, and an optional
  (off by default) script that reloads a page when it detects a translation, giving
  up if the translation comes straight back.
- Each technique can be switched on or off under Site administration > Plugins >
  Local plugins > Never translate, with a "Select all/none" toggle.
