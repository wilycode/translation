# WilyCode Translation

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://github.com/wilycode/translation/actions/workflows/tests.yml/badge.svg)](https://github.com/wilycode/translation/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/wilycode/translation)](https://packagist.org/packages/wilycode/translation)

**Lightweight translation and localization contracts for PHP 8.4+.** Define how your application translates messages, chooses plural forms, and formats regional data—without tying it to a framework or storage backend.

> **WilyCode PHP Ecosystem**
>
> Part of **[WilyCode](https://github.com/wilycode/wilycode)**, a collection of independent, reusable PHP packages with their own repositories, versions, and Composer installations.
>
> **[Explore the WilyCode ecosystem →](https://github.com/wilycode/wilycode)**

## Table of contents

- [Introduction](#introduction)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Translation](#translation)
- [Pluralization](#pluralization)
- [Message formatting](#message-formatting)
- [Locale formatting](#locale-formatting)
- [API reference](#api-reference)
- [Integrations](#integrations)
- [Limitations & security](#limitations--security)
- [Development & license](#development--license)

## Introduction

`wilycode/translation` provides **three interfaces, one enum, and one helper**. It is the common foundation for translation implementations, not a translation engine by itself.

| Component | Purpose |
| --- | --- |
| `TranslatorInterface` | Retrieve messages and pluralized messages by resource and key. |
| `LanguageInterface` | Identify a language and select its plural-form index. |
| `LocaleFormatterInterface` | Format dates, date-times, money, and numbers. |
| `DateStyle` | Choose `Short`, `Medium`, or `Long` date formatting. |
| `MessageFormatter` | Substitute named `{placeholders}` in a message. |

Translation catalogs, concrete language rules, and locale formatting implementations live in separate packages or in your application.

## Installation

**Requirements:** PHP 8.4 or newer. No framework, database, or third-party runtime package is required. The contracts themselves do not require `ext-intl`; particular implementations may.

```bash
composer require wilycode/translation
```

Install a concrete translator or formatter separately when your application needs one. This package does not provide ready-to-use translations.

## Quick start

### Depend on the translator contract

Type-hint `TranslatorInterface` in application services. Supply a concrete implementation through your dependency injection configuration:

```php
<?php

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

Here `messages` is a resource and `welcome` is a translation key. **Their meaning, translation lookup, and fallback behavior depend on the translator implementation.** The example defines a consumer, not a standalone translator.

### Use the included helper immediately

`MessageFormatter` works without a translation backend:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use WilyCode\Translation\MessageFormatter;

$message = MessageFormatter::format(
    'Hello, {name}! You have {count} messages.',
    ['name' => 'Ada', 'count' => 3],
);

echo $message; // Hello, Ada! You have 3 messages.
```

## Translation

`TranslatorInterface` has two methods: `translate()` and `translatePlural()`. They both return a string and accept named interpolation parameters.

```php
use WilyCode\Translation\TranslatorInterface;

function pageTitle(TranslatorInterface $translator): string
{
    return $translator->translate('pages', 'home.title');
}
```

The interface does not prescribe file structure, supported locales, current-language selection, missing-key behavior, or automatic placeholder replacement. An implementation makes those decisions.

## Pluralization

The translator selects pluralized messages via `translatePlural()`; a language implementation provides plural-form rules via `LanguageInterface`.

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

**Pass `count` as a parameter explicitly** if your message contains `{count}`. The interface does not add it automatically.

`LanguageInterface` provides the language code, number of plural forms, and index for a given count. For example, an application-defined English rule could be:

```php
use WilyCode\Translation\LanguageInterface;

final class EnglishLanguage implements LanguageInterface
{
    public function getCode(): string { return 'en'; }

    public function getNumberOfPluralForms(): int { return 2; }

    public function getPluralForm(int $count): int
    {
        return $count === 1 ? 0 : 1;
    }
}
```

The index should be between `0` and `getNumberOfPluralForms() - 1`. **No concrete language definitions ship in this package.**

## Message formatting

`MessageFormatter::format()` replaces tokens such as `{name}` using literal string replacement; unknown tokens are preserved.

```php
use WilyCode\Translation\MessageFormatter;

MessageFormatter::format('Order #{id}: {status}', [
    'id' => 42,
    'status' => 'ready',
]); // 'Order #42: ready'

MessageFormatter::format('Hello, {name}!', []);
// 'Hello, {name}!'
```

| Parameter value | Replacement |
| --- | --- |
| `'Ada'` | `Ada` |
| `42` or `1.5` | PHP string representation |
| `true` | `1` |
| `false` or `null` | Empty text (`''`) |
| Missing parameter | Original `{placeholder}` stays unchanged |

The helper accepts scalar or `null` values. It does **not** choose a translation, interpret ICU MessageFormat expressions, perform locale formatting, or escape HTML. A translator implementation may choose to use this helper, but the interface does not require it.

## Locale formatting

`LocaleFormatterInterface` abstracts regional display of dates, date-times, prices, and numbers. Inject an implementation rather than instantiating the interface:

```php
use WilyCode\Translation\DateStyle;
use WilyCode\Translation\LocaleFormatterInterface;

function invoiceSummary(
    LocaleFormatterInterface $formatter,
    DateTimeInterface $date,
    float $amount,
): string {
    return $formatter->formatDate($date, DateStyle::Long)
        . ' — '
        . $formatter->formatMoney($amount, 'PLN', 2);
}
```

Date styles are a string-backed enum:

| Enum case | Value |
| --- | --- |
| `DateStyle::Short` | `short` |
| `DateStyle::Medium` | `medium` |
| `DateStyle::Long` | `long` |

Each formatter determines its own date patterns, decimal separators, and currency presentation. `getDateFormat()` returns a PHP `DateTime` format pattern.

## API reference

### TranslatorInterface

| Method | Returns | Description |
| --- | --- | --- |
| `translate(string $resource, string $key, array $parameters = [])` | `string` | Translate a keyed message. |
| `translatePlural(string $resource, string $key, int $count, array $parameters = [])` | `string` | Translate a keyed plural message. |

Both parameter arrays are intended for `array<string, scalar|null>`.

### LanguageInterface

| Method | Returns | Description |
| --- | --- | --- |
| `getCode()` | `string` | Language code, e.g. `en` or `pl`. |
| `getNumberOfPluralForms()` | `int` | Number of plural forms. |
| `getPluralForm(int $count)` | `int` | Plural index for the count. |

### LocaleFormatterInterface

| Method | Returns | Description |
| --- | --- | --- |
| `getLocale()` | `string` | Locale identifier, e.g. `en-US`. |
| `getDateFormat(DateStyle $style = DateStyle::Medium)` | `string` | PHP date format pattern. |
| `formatDate(DateTimeInterface $date, DateStyle $style = DateStyle::Medium)` | `string` | Localized date. |
| `formatDateTime(DateTimeInterface $dateTime, DateStyle $style = DateStyle::Medium)` | `string` | Localized date and time. |
| `formatMoney(float\|int $amount, ?string $currency = null, ?int $fractionDigits = null)` | `string` | Currency/amount formatting. |
| `formatNumber(float\|int $number, int $fractionDigits = 2)` | `string` | Number formatting. |

### Included helper and enum

| Member | Returns | Description |
| --- | --- | --- |
| `MessageFormatter::format(string $message, array $parameters)` | `string` | Replace named placeholders. |
| `DateStyle::Short`, `Medium`, `Long` | `DateStyle` | Date style enum cases. |

## Integrations

The core package does not require any of these adapters. Install only those your application needs, when available:

| Package | Purpose |
| --- | --- |
| [`wilycode/translation-array`](https://github.com/wilycode/translation-array) | Array-backed message translations. |
| [`wilycode/translation-database`](https://github.com/wilycode/translation-database) | Database-backed message translations. |
| [`wilycode/translation-languages`](https://github.com/wilycode/translation-languages) | Concrete language rules and locale formatters. |

```text
                       Application
                           |
                WilyCode Translation
          (interfaces + MessageFormatter)
                  /        |        \
       Array adapter   Database adapter   Languages/formatters
             (optional implementations)
```

Applications depend on `TranslatorInterface` and `LocaleFormatterInterface`, while individual adapters provide the implementations. This keeps storage and regional formatting separate from business logic.

## Limitations & security

- This package **does not** load translation catalogs, choose a locale, define fallback rules, or provide concrete translators and formatters.
- `MessageFormatter` is literal substitution, **not HTML escaping**. Escape untrusted text for its output context before rendering.
- Format currency and numbers with a locale formatter, not with the message helper.
- Avoid exposing or logging translation strings or interpolation parameters when they contain sensitive data.
- Missing-key and plural-catalog behavior depend on your installed translator implementation.

## Development & license

```bash
composer install
composer validate --strict
composer test
```

PHPUnit runs on PHP 8.4 in [GitHub Actions](https://github.com/wilycode/translation/actions). Tests are retained in Git but excluded from distribution archives.

Contributions are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md) or open an [issue](https://github.com/wilycode/translation/issues).

MIT © 2026 Simon Piter. See [LICENSE](LICENSE).
