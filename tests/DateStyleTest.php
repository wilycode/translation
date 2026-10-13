<?php

declare(strict_types=1);

namespace WilyCode\Translation\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WilyCode\Translation\DateStyle;

#[CoversClass(DateStyle::class)]
final class DateStyleTest extends TestCase
{
    public function test_exposes_three_string_backed_styles(): void
    {
        self::assertSame('short', DateStyle::Short->value);
        self::assertSame('medium', DateStyle::Medium->value);
        self::assertSame('long', DateStyle::Long->value);
        self::assertSame(DateStyle::Long, DateStyle::from('long'));
        self::assertCount(3, DateStyle::cases());
    }
}
