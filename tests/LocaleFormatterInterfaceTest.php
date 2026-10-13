<?php

declare(strict_types=1);

namespace WilyCode\Translation\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WilyCode\Translation\DateStyle;
use WilyCode\Translation\LocaleFormatterInterface;

#[CoversClass(LocaleFormatterInterface::class)]
final class LocaleFormatterInterfaceTest extends TestCase
{
    public function test_can_be_implemented_and_used(): void
    {
        $formatter = new class implements LocaleFormatterInterface {
            public function getLocale(): string
            {
                return 'en-US';
            }

            public function getDateFormat(DateStyle $style = DateStyle::Medium): string
            {
                return 'Y-m-d|' . $style->value;
            }

            public function formatDate(\DateTimeInterface $date, DateStyle $style = DateStyle::Medium): string
            {
                // Minimal test implementation to verify the interface contract.
                return $date->format('Y-m-d') . '|' . $style->value;
            }

            public function formatDateTime(\DateTimeInterface $dateTime, DateStyle $style = DateStyle::Medium): string
            {
                return $dateTime->format('Y-m-d H:i:s') . '|' . $style->value;
            }

            public function formatMoney(float|int $amount, ?string $currency = null, ?int $fractionDigits = null): string
            {
                $currency        ??= 'XXX';
                $fractionDigits  ??= 2;

                return sprintf('%s %.' . $fractionDigits . 'f', $currency, (float) $amount);
            }

            public function formatNumber(float|int $number, int $fractionDigits = 2): string
            {
                return number_format((float) $number, $fractionDigits, '.', '');
            }
        };

        $date = new \DateTimeImmutable('2026-01-29 12:34:56');

        self::assertSame('en-US', $formatter->getLocale());
        self::assertSame('Y-m-d|medium', $formatter->getDateFormat());
        self::assertSame('2026-01-29|medium', $formatter->formatDate($date));
        self::assertSame('2026-01-29 12:34:56|short', $formatter->formatDateTime($date, DateStyle::Short));

        self::assertSame('PLN 1234.57', $formatter->formatMoney(1234.567, 'PLN', 2));
        self::assertSame('1234.57', $formatter->formatNumber(1234.567, 2));
    }
}
