# WilyCode Translation

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://github.com/wilycode/translation/actions/workflows/tests.yml/badge.svg)](https://github.com/wilycode/translation/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/wilycode/translation)](https://packagist.org/packages/wilycode/translation)

**Framework-agnostic translation contracts and configuration-based locale formatting for PHP 8.4+.** Build translators, choose plural forms, and format dates, numbers, and money without coupling your application to a framework or translation storage backend.

> ### WilyCode PHP Ecosystem
>
> Part of **[WilyCode](https://github.com/wilycode/wilycode)** — independent, reusable PHP packages, each with its own repository, versioning, and Composer installation.
>
> **[Explore the WilyCode PHP Ecosystem →](https://github.com/wilycode/wilycode)**

## Table of contents

- [Introduction](#introduction)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Translation and pluralization](#translation-and-pluralization)
- [Locale formatting](#locale-formatting)
- [Locale resources and fallback](#locale-resources-and-fallback)
- [Using only selected languages](#using-only-selected-languages)
- [Message formatting](#message-formatting)
- [API reference](#api-reference)
- [Integrations](#integrations)
- [Limitations and security](#limitations-and-security)
- [Development and license](#development-and-license)

## Introduction

`wilycode/translation` supplies reusable **contracts, a message formatting helper, and two concrete locale formatting classes**. It does not contain language definitions, translated messages, or regional configuration data.

| Component | Purpose |
| --- | --- |
| `TranslatorInterface` | Retrieve translated and pluralized messages by resource and key. |
| `LanguageInterface` | Determine a language's plural-form index for an integer count. |
| `LocaleFormatterInterface` | Common contract for regional date, number, and currency formatting. |
| `DateStyle` | `Short`, `Medium`, and `Long` date/time styles. |
| `MessageFormatter` | Replace named placeholders such as `{name}`. |
| `LocaleFormatter\LocaleFormatter` | **Concrete**, configurable date, number, and currency formatting. |
| `LocaleFormatter\LocaleFormatterFactory` | Load a locale configuration file by code with a configurable fallback. |

`LocaleFormatter` and its factory are intentionally in the base package because they do not depend on any particular language. Ready-made language rules and regional resources live in [`wilycode/translation-languages`](https://github.com/wilycode/translation-languages), or you can supply your own.

## Installation

**Requirements:** PHP 8.4+. No framework, database, `ext-intl`, or other runtime dependency is required.

```bash
composer require wilycode/translation:^0.1.1
```

**Version 0.1.1 or newer is required for `LocaleFormatter` and `LocaleFormatterFactory`.** Version 0.1.0 provided only the contracts, `DateStyle`, and `MessageFormatter`.

This install provides formatter code but **no locale configuration files**. Create your own resource files or copy only the ones you need from the separate languages repository.

## Quick start

### Format a date and an amount

`LocaleFormatter` accepts a plain PHP configuration array; it can be used without any other WilyCode package:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use WilyCode\Translation\DateStyle;
use WilyCode\Translation\LocaleFormatter\LocaleFormatter;

$formatter = new LocaleFormatter([
    'locale' => 'pl-PL',
    'date' => [
        'formats' => [
            'short' => 'Y-m-d',
            'medium' => 'd.m.Y',
            'long' => 'j.m.Y',
        ],
    ],
    'number' => [
        'decimal_separator' => ',',
        'thousands_separator' => ' ',
        'currency' => 'PLN',
        'fraction_digits' => 2,
    ],
]);

$date = new DateTimeImmutable('2026-10-14');

echo $formatter->formatDate($date);              // 14.10.2026
echo $formatter->formatDate($date, DateStyle::Short); // 2026-10-14
echo $formatter->formatMoney(1234.5);            // PLN 1 234,50
```

### Use a resource directory

Instead of constructing arrays manually, create `resources/locales/pl-pl.php` that returns a configuration array, and let the factory load it:

```php
use WilyCode\Translation\LocaleFormatter\LocaleFormatterFactory;

$factory = new LocaleFormatterFactory(
    __DIR__ . '/resources/locales',
    'pl-PL',
);

$formatter = $factory->forCode('pl-PL');
```

The directory and resource file must exist. To use prebuilt files, see [Using only selected languages](#using-only-selected-languages).

## Translation and pluralization

### Translate a message

Use the `TranslatorInterface` contract in application services. Supply a concrete translator implementation separately through dependency injection:

```php
use WilyCode\Translation\TranslatorInterface;

final readonly class WelcomeService
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    public function message(string $name): string
    {
        return $this->translator->translate(
            'messages',
            'welcome',
            ['name' => $name],
        );
    }
}
```

A translator implementation decides where to find messages, how to select a language, and how to handle missing keys. This example is a **consumer**, not a working translation backend.

### Translate plural forms

```php
use WilyCode\Translation\TranslatorInterface;

function itemSummary(TranslatorInterface $translator, int $count): string
{
    return $translator->translatePlural(
        'shop',
        'items',
        $count,
        ['count' => $count],
    );
}
```

Pass `{count}` as a parameter explicitly if the translated message uses it. `LanguageInterface` specifies `getCode()`, `getNumberOfPluralForms()`, and `getPluralForm(int $count)`; its zero-based plural indexes must correspond to the forms provided by the translator.

The base package does **not** ship any concrete language rules or message catalogs.

## Locale formatting

The concrete `LocaleFormatter` implements `LocaleFormatterInterface` and reads its behavior from a PHP array. It provides:

| Operation | Example with Polish settings | Notes |
| --- | --- | --- |
| `formatDate($date)` | `14.10.2026` | `DateStyle::Medium` by default. |
| `formatDate($date, DateStyle::Long)` | `14 października 2026 r.` | Requires a suitable long format and month-name map. |
| `formatDateTime($dateTime)` | `14.10.2026 12:34:56` | Combines date and configured time pattern. |
| `formatNumber(1234.5)` | `1 234,50` | Configurable separators and decimal places. |
| `formatMoney(1234.5)` | `PLN 1 234,50` | Outputs currency code, then number. |
| `getLocale()` | `pl-PL` | Locale identifier from configuration. |

### Configuration structure

```php
<?php

return [
    'locale' => 'en-US',
    'date' => [
        'formats' => [
            'short' => 'Y-m-d',
            'medium' => 'm/d/Y',
            'long' => 'F j, Y',
        ],
        // Optional: 'long_month_case' => 'genitive' or 'nom',
        // Optional: 'months_genitive' => [1 => '...', /* ... */],
        // Optional: 'months_nominative' => [1 => '...', /* ... */],
    ],
    'time' => [
        'formats' => ['short' => 'H:i', 'medium' => 'H:i:s', 'long' => 'H:i:s'],
    ],
    'number' => [
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'currency' => 'USD',
        'fraction_digits' => 2,
        // Optional: 'grouping' => [3, 2], // Indian-style digit grouping.
    ],
];
```

`getDateFormat()` returns a **PHP `DateTime` pattern**, not an ICU/CLDR pattern. Long-date month substitution uses configured maps when available. Short and medium formats follow PHP formatting. The formatter uses PHP's `number_format()` for standard grouping and supports `[3, 2]` for Indian grouping.

`DateStyle` is a string-backed enum: `Short = 'short'`, `Medium = 'medium'`, `Long = 'long'`. Missing settings use built-in defaults, rather than automatically selecting a regional locale.

## Locale resources and fallback

The factory takes a directory containing trusted PHP files such as `pl-pl.php`, `en-us.php`, and optional base-language aliases such as `pl.php`:

```text
resources/
└── locales/
    ├── pl-pl.php
    ├── en-us.php
    └── pl.php       # Optional alias: return require __DIR__ . '/pl-pl.php';
```

```php
use WilyCode\Translation\LocaleFormatter\LocaleFormatterFactory;

$factory = new LocaleFormatterFactory(__DIR__ . '/resources/locales', 'en-US');

$pl = $factory->forCode('pl-PL');  // pl-pl.php
$en = $factory->forCode('EN_us');  // en-us.php
$fallback = $factory->forCode('xx'); // en-us.php
```

Lookup order: **exact normalized code**, **base-language alias**, then **configured default code**. Codes are case-insensitive; underscores are normalized to hyphens. Invalid codes, including path traversal strings, are treated as missing and fall back to the default. The factory throws `RuntimeException` if its directory is missing or no matching resource exists.

The factory reads from exactly **one directory**; it does not merge local overrides with vendor resources. Alias files that use `require` need their referenced regional files alongside them.

## Using only selected languages

The [WilyCode Translation Languages repository](https://github.com/wilycode/translation-languages) contains ready-to-use language rules for 25 languages and configuration resources for 27 locales. **You do not have to install this entire package.**

For a Polish-only application, keep `wilycode/translation` installed, and copy just these two files from that repository:

| Source | Application destination | Purpose |
| --- | --- | --- |
| [`src/Language/Pl.php`](https://github.com/wilycode/translation-languages/blob/main/src/Language/Pl.php) | `app/Translation/Language/Pl.php` | Polish plural categories. |
| [`src/resources/locales/pl-pl.php`](https://github.com/wilycode/translation-languages/blob/main/src/resources/locales/pl-pl.php) | `resources/locales/pl-pl.php` | Polish formatting configuration. |

For the copied language class, change its namespace to `App\Translation\Language` and ensure Composer autoloads `App\` from `app/`. Keep its `use WilyCode\Translation\LanguageInterface;` unchanged. The regional resource file is a plain PHP array and does not need any namespace adjustment.

```php
use App\Translation\Language\Pl;
use WilyCode\Translation\LocaleFormatter\LocaleFormatterFactory;

$language = new Pl();
$formatter = (new LocaleFormatterFactory(__DIR__ . '/resources/locales', 'pl-PL'))
    ->forCode('pl-PL');

$language->getPluralForm(2);            // 1
$formatter->formatMoney(1234.5);       // PLN 1 234,50
```

**If you only format dates and numbers, skip `Pl.php`. If you only need plural categories, skip `pl-pl.php`.** No other language files are necessary. See the languages repository README for full selective-install instructions and an example of adding a new language.

## Message formatting

The independent `MessageFormatter` helper replaces named placeholders with string values and preserves unknown placeholders:

```php
use WilyCode\Translation\MessageFormatter;

MessageFormatter::format('Hello, {name}!', ['name' => 'Ada']); // 'Hello, Ada!'
MessageFormatter::format('Hello, {name}!', []); // 'Hello, {name}!'
```

| Value | Replacement |
| --- | --- |
| String, integer or float | Its string form. |
| `true` | `1` |
| `false`, `null` | Empty string. |
| Missing parameter | Placeholder remains unchanged. |

This helper is not a translator, an ICU MessageFormat parser, or an HTML escaping function.

## API reference

### Translation contracts

| Interface | Method | Returns |
| --- | --- | --- |
| `TranslatorInterface` | `translate(string $resource, string $key, array $parameters = [])` | `string` |
| `TranslatorInterface` | `translatePlural(string $resource, string $key, int $count, array $parameters = [])` | `string` |
| `LanguageInterface` | `getCode()` | `string` |
| `LanguageInterface` | `getNumberOfPluralForms()` | `int` |
| `LanguageInterface` | `getPluralForm(int $count)` | `int` |

### Locale formatter

| Method | Returns | Description |
| --- | --- | --- |
| `LocaleFormatter::getLocale()` | `string` | Current locale identifier. |
| `LocaleFormatter::getDateFormat(DateStyle $style = DateStyle::Medium)` | `string` | PHP date format. |
| `LocaleFormatter::formatDate(DateTimeInterface $date, DateStyle $style = DateStyle::Medium)` | `string` | Formatted date. |
| `LocaleFormatter::formatDateTime(DateTimeInterface $dateTime, DateStyle $style = DateStyle::Medium)` | `string` | Formatted date and time. |
| `LocaleFormatter::formatNumber(float\|int $number, int $fractionDigits = 2)` | `string` | Formatted number. |
| `LocaleFormatter::formatMoney(float\|int $amount, ?string $currency = null, ?int $fractionDigits = null)` | `string` | Formatted currency and amount. |

`LocaleFormatterInterface` defines these same six methods. Concrete `LocaleFormatter` accepts `array $config` in its constructor.

### Resource factory and helpers

| Member | Purpose |
| --- | --- |
| `new LocaleFormatterFactory(string $resourcesDir, string $defaultCode = 'en-US')` | Set resource directory and fallback code. |
| `LocaleFormatterFactory::forCode(?string $code): LocaleFormatter` | Load the resource for the requested locale. |
| `MessageFormatter::format(string $message, array $parameters)` | Replace message placeholders. |
| `DateStyle::Short`, `Medium`, `Long` | Date formatting style enum cases. |

## Integrations

| Package | Purpose |
| --- | --- |
| [`wilycode/translation-languages`](https://github.com/wilycode/translation-languages) | Language pluralization rules and locale configuration data. |
| [`wilycode/translation-array`](https://github.com/wilycode/translation-array) | Array-backed message translator (separate package). |
| [`wilycode/translation-database`](https://github.com/wilycode/translation-database) | Database-backed message translator (separate package). |

```text
                       Application
                           |
                WilyCode Translation
            (contracts + formatters + helper)
                     /           \\
         Selective files      Full languages package
            (optional)           (optional)
```

Install the full languages package if you want prebuilt definitions for many languages. Copy a selected class and locale file if you want a minimal application footprint. Neither approach automatically implements message lookup or user-language selection.

## Limitations and security

- `LocaleFormatter` uses PHP date patterns and explicit configuration; **it is not a complete ICU/CLDR formatter** and does not handle language-specific digit shaping or currency exchange rates.
- `formatMoney()` prefixes the ISO currency code (`PLN 1 234,50`), not a localized currency symbol or position.
- `LocaleFormatterFactory` loads trusted **executable PHP configuration**. Do not point it at untrusted or writable-by-users directories.
- Date pattern defaults and missing month maps may produce English month names. Copy suitable regional data for localized long dates.
- Factory fallback is for **locale configuration**, not translation-message fallback. Neither translator catalogs nor `ext-intl` is included.
- `MessageFormatter` does **not escape HTML**. Escape interpolated values for their output context before rendering.
- Copying individual files means **you maintain updates to those copies**; Composer cannot update them on your behalf. Preserve the MIT license notice.

## Development and license

```bash
composer validate --strict
composer install
composer test
```

GitHub Actions runs PHPUnit on PHP 8.4. See [CONTRIBUTING.md](CONTRIBUTING.md) and [CHANGELOG.md](CHANGELOG.md).

MIT © 2026 Simon Piter. See [LICENSE](LICENSE).
