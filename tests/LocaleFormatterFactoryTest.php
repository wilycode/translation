<?php

declare(strict_types=1);

namespace WilyCode\Translation\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WilyCode\Translation\LocaleFormatter\LocaleFormatter;
use WilyCode\Translation\LocaleFormatter\LocaleFormatterFactory;

#[CoversClass(LocaleFormatterFactory::class)]
final class LocaleFormatterFactoryTest extends TestCase
{
    private const RESOURCES = __DIR__ . '/fixtures/locales';

    public function test_loads_specific_region_and_normalizes_code(): void
    {
        $factory = new LocaleFormatterFactory(self::RESOURCES);
        $formatter = $factory->forCode(' PL_pl ');
        self::assertInstanceOf(LocaleFormatter::class, $formatter);
        self::assertSame('pl-PL', $formatter->getLocale());
        self::assertSame('PLN 1 234,50', $formatter->formatMoney(1234.5));
    }

    public function test_base_language_alias_uses_regional_configuration(): void
    {
        $factory = new LocaleFormatterFactory(self::RESOURCES);
        self::assertSame('pl-PL', $factory->forCode('pl')->getLocale());
    }

    public function test_unknown_invalid_and_empty_codes_fall_back(): void
    {
        $factory = new LocaleFormatterFactory(self::RESOURCES);
        foreach (['xx-ZZ', '../other', '', null] as $code) {
            self::assertSame('en-US', $factory->forCode($code)->getLocale());
        }
    }

    public function test_missing_directory_throws(): void
    {
        $this->expectException(RuntimeException::class);
        new LocaleFormatterFactory(__DIR__ . '/missing-directory');
    }

    public function test_missing_default_resource_throws(): void
    {
        $factory = new LocaleFormatterFactory(self::RESOURCES, 'missing-ZZ');
        $this->expectException(RuntimeException::class);
        $factory->forCode('xx-ZZ');
    }
}
