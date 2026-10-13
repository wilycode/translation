<?php

declare(strict_types=1);

namespace WilyCode\Translation\Tests;

use PHPUnit\Framework\TestCase;
use WilyCode\Translation\LanguageInterface;

final class LanguageInterfaceTest extends TestCase
{
    public function test_can_define_plural_forms(): void
    {
        $language = new class implements LanguageInterface {
            public function getCode(): string { return 'en'; }
            public function getNumberOfPluralForms(): int { return 2; }
            public function getPluralForm(int $count): int { return $count === 1 ? 0 : 1; }
        };

        self::assertSame('en', $language->getCode());
        self::assertSame(2, $language->getNumberOfPluralForms());
        self::assertSame(0, $language->getPluralForm(1));
        self::assertSame(1, $language->getPluralForm(0));
        self::assertSame(1, $language->getPluralForm(3));
    }
}
