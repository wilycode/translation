# Changelog

All notable changes to WilyCode Translation are documented here.

## [0.1.1] - 2026-10-14

### Added

- `LocaleFormatter` for config-driven date, time, number, and currency formatting, including localized long-date month maps and Indian digit grouping.
- `LocaleFormatterFactory` for loading trusted application-provided locale resources with normalized codes and fallback.
- Unit tests for the shared formatter, factory, and independent test locale resources.
- Documentation for using only selected language and locale files from `translation-languages` without installing the full package.

### Notes

- Existing interfaces, `DateStyle`, and `MessageFormatter` remain unchanged.
- The base package does not bundle language definitions or region-specific configuration files.

## [0.1.0] - 2026-10-13

### Added

- Contracts for translation, pluralization and locale-aware formatting.
- `DateStyle` enum with `Short`, `Medium` and `Long` values.
- `MessageFormatter` for named placeholder substitution.
- PHPUnit tests for contracts, date styles and message substitution.
- GitHub Actions tests and English documentation.

[0.1.1]: https://github.com/wilycode/translation/releases/tag/v0.1.1
[0.1.0]: https://github.com/wilycode/translation/releases/tag/v0.1.0
