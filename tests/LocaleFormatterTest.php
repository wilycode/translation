<?php

declare(strict_types=1);

namespace WilyCode\Translation\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WilyCode\Translation\DateStyle;
use WilyCode\Translation\LocaleFormatter\LocaleFormatter;
use WilyCode\Translation\LocaleFormatterInterface;

#[CoversClass(LocaleFormatter::class)]
final class LocaleFormatterTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function configuration(): array
    {
        return [
            'locale' => 'test-XX',
            'date' => [
                'formats' => ['short' => 'Y-m-d', 'medium' => 'd.m.Y', 'long' => 'j F Y'],
                'long_month_case' => 'genitive',
                'months_genitive' => [1 => 'MonthOne'],
                'months_nominative' => [1 => 'MonthNom'],
            ],
            'time' => ['formats' => ['short' => 'H:i', 'medium' => 'H:i:s', 'long' => 'H:i:s']],
            'number' => [
                'decimal_separator' => ',',
                'thousands_separator' => ' ',
                'currency' => 'TST',
                'fraction_digits' => 2,
            ],
        ];
    }

    public function test_implements_contract_and_formats_values(): void
    {
        $formatter = new LocaleFormatter(self::configuration());
        $date = new DateTimeImmutable('2026-01-29 12:34:56');

        self::assertInstanceOf(LocaleFormatterInterface::class, $formatter);
        self::assertSame('test-XX', $formatter->getLocale());
        self::assertSame('d.m.Y', $formatter->getDateFormat());
        self::assertSame('2026-01-29', $formatter->formatDate($date, DateStyle::Short));
        self::assertSame('29.01.2026', $formatter->formatDate($date));
        self::assertSame('29 MonthOne 2026', $formatter->formatDate($date, DateStyle::Long));
        self::assertSame('29.01.2026 12:34:56', $formatter->formatDateTime($date));
        self::assertSame('2026-01-29 12:34', $formatter->formatDateTime($date, DateStyle::Short));
        self::assertSame('TST 1 234,50', $formatter->formatMoney(1234.5));
        self::assertSame('USD 1 234,500', $formatter->formatMoney(1234.5, 'USD', 3));
        self::assertSame('1 234,50', $formatter->formatNumber(1234.5));
        self::assertSame('-1 234,50', $formatter->formatNumber(-1234.5));
    }

    public function test_supports_nominative_month_case(): void
    {
        $config = self::configuration();
        $config['date']['long_month_case'] = 'nom';
        self::assertSame(
            '29 MonthNom 2026',
            (new LocaleFormatter($config))->formatDate(new DateTimeImmutable('2026-01-29'), DateStyle::Long),
        );
    }

    public function test_supports_indian_digit_grouping(): void
    {
        $config = self::configuration();
        $config['number']['decimal_separator'] = '.';
        $config['number']['thousands_separator'] = ',';
        $config['number']['grouping'] = [3, 2];
        $formatter = new LocaleFormatter($config);
        self::assertSame('12,34,567.89', $formatter->formatNumber(1234567.89));
        self::assertSame('-12,34,567.89', $formatter->formatNumber(-1234567.89));
    }

    public function test_uses_default_patterns_when_configuration_is_missing(): void
    {
        $formatter = new LocaleFormatter([]);
        $date = new DateTimeImmutable('2026-07-08 12:34:56');
        self::assertSame('en-US', $formatter->getLocale());
        self::assertSame('Y-m-d', $formatter->getDateFormat());
        self::assertSame('2026-07-08', $formatter->formatDate($date));
        self::assertSame('2026-07-08 12:34:56', $formatter->formatDateTime($date));
        self::assertSame('USD 1,234.50', $formatter->formatMoney(1234.5));
    }
}
