<?php

declare(strict_types=1);

namespace WilyCode\Translation\LocaleFormatter;

use DateTimeInterface;
use WilyCode\Translation\DateStyle;
use WilyCode\Translation\LocaleFormatterInterface;

class LocaleFormatter implements LocaleFormatterInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(
        protected array $config
    ) {
    }

    public function getLocale(): string
    {
        return (string) ($this->config['locale'] ?? 'en-US');
    }

    public function getDateFormat(DateStyle $style = DateStyle::Medium): string
    {
        return (string) ($this->config['date']['formats'][$style->value]
            ?? $this->config['date']['formats'][DateStyle::Medium->value]
            ?? 'Y-m-d');
    }

    public function formatDate(DateTimeInterface $date, DateStyle $style = DateStyle::Medium): string
    {
        $format = $this->getDateFormat($style);

        // The configured month-name map is optional; without it, PHP uses English month names.
        $monthCase = (string) ($this->config['date']['long_month_case'] ?? 'genitive');
        if ($style === DateStyle::Long && ($monthCase === 'genitive' || $monthCase === 'nom')) {
            return $this->formatLongDateWithMonthMap($date, $format);
        }

        return $date->format($format);
    }

    public function formatDateTime(DateTimeInterface $dateTime, DateStyle $style = DateStyle::Medium): string
    {
        $date = $this->formatDate($dateTime, $style);
        $time = $dateTime->format($this->timeFormat($style));

        return trim($date . ' ' . $time);
    }

    public function formatMoney(float|int $amount, ?string $currency = null, ?int $fractionDigits = null): string
    {
        $currency       ??= (string) ($this->config['number']['currency'] ?? 'USD');
        $fractionDigits ??= (int) ($this->config['number']['fraction_digits'] ?? 2);

        return $currency . ' ' . $this->formatNumber($amount, $fractionDigits);
    }

    public function formatNumber(float|int $number, int $fractionDigits = 2): string
    {
        $decimalSeparator = (string) ($this->config['number']['decimal_separator'] ?? '.');
        $thousandsSeparator = (string) ($this->config['number']['thousands_separator'] ?? ',');
        $grouping = $this->config['number']['grouping'] ?? [3];

        if ($grouping !== [3, 2]) {
            return number_format((float) $number, $fractionDigits, $decimalSeparator, $thousandsSeparator);
        }

        // Indian numbering: the final group has three digits; preceding groups have two.
        $formatted = number_format((float) $number, $fractionDigits, '.', '');
        [$integer, $fraction] = array_pad(explode('.', $formatted, 2), 2, null);
        $sign = str_starts_with($integer, '-') ? '-' : '';
        $digits = ltrim($integer, '-');
        $end = substr($digits, -3);
        $start = substr($digits, 0, -3);
        $grouped = $start === ''
            ? $end
            : preg_replace('/\B(?=(?:\d{2})+(?!\d))/', $thousandsSeparator, $start) . $thousandsSeparator . $end;

        return $sign . $grouped . ($fraction !== null ? $decimalSeparator . $fraction : '');
    }

    protected function timeFormat(DateStyle $style): string
    {
        return (string) ($this->config['time']['formats'][$style->value]
            ?? $this->config['time']['formats'][DateStyle::Medium->value]
            ?? 'H:i:s');
    }

    protected function formatLongDateWithMonthMap(DateTimeInterface $date, string $format): string
    {
        $monthNumber = (int) $date->format('n');
        $monthCase   = (string) ($this->config['date']['long_month_case'] ?? 'genitive');
        $mapKey      = $monthCase === 'nom' ? 'months_nominative' : 'months_genitive';
        $months      = $this->config['date'][$mapKey] ?? [];
        $monthName   = is_array($months) ? ($months[$monthNumber] ?? null) : null;

        if (!is_string($monthName) || $monthName === '') {
            return $date->format($format);
        }

        return strtr($date->format($format), [
            $date->format('F') => $monthName,
            $date->format('M') => $monthName,
        ]);
    }
}
