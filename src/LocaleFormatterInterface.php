<?php

declare(strict_types=1);

namespace WilyCode\Translation;

interface LocaleFormatterInterface
{
    /**
     * BCP 47 locale code used by the current formatter, e.g. en-US or pl-PL.
     */
    public function getLocale(): string;

    /**
     * PHP DateTime format used by server-side formatting and validation.
     */
    public function getDateFormat(DateStyle $style = DateStyle::Medium): string;

    public function formatDate(\DateTimeInterface $date, DateStyle $style = DateStyle::Medium): string;

    public function formatDateTime(\DateTimeInterface $dateTime, DateStyle $style = DateStyle::Medium): string;

    public function formatMoney(float|int $amount, ?string $currency = null, ?int $fractionDigits = null): string;

    public function formatNumber(float|int $number, int $fractionDigits = 2): string;
}
