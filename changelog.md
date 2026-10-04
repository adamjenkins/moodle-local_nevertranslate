# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [0.1.2] - 2026-10-04

### Changed

- Maturity raised from Alpha to Beta.
- Continuous integration tests against the released Moodle 5.3
  (MOODLE_503_STABLE) instead of Moodle's development branch.
- composer.json: the moodle/moodle requirement uses a caret constraint (`^5.2`).

## [0.1.1] - 2026-10-04

### Changed

- Declare Moodle 5.3 support.

## [0.1.0] - 2026-09-26

### Added

- Sitewide injection of anti-translation techniques: `translate="no"` and
  `class="notranslate"` on `<html>` and `<body>`,
  `<meta name="google" content="notranslate">`, `<meta name="robots"
  content="notranslate">`, the `X-Robots-Tag: notranslate` header, a guard script,
  and an opt-in revert-by-reload script.
- Admin setting to choose the techniques, with a "Select all/none" toggle.
