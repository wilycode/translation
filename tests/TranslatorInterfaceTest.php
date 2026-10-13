<?php

declare(strict_types=1);

namespace WilyCode\Translation\Tests;

use PHPUnit\Framework\TestCase;
use WilyCode\Translation\MessageFormatter;
use WilyCode\Translation\TranslatorInterface;

final class TranslatorInterfaceTest extends TestCase
{
    public function test_can_be_implemented_with_named_and_plural_translations(): void
    {
        $translator = new class implements TranslatorInterface {
            public function translate(string $resource, string $key, array $parameters = []): string
            {
                if ($resource !== 'shop' || $key !== 'hello') {
                    return $key;
                }

                return MessageFormatter::format('Hello {name}', $parameters);
            }

            public function translatePlural(string $resource, string $key, int $count, array $parameters = []): string
            {
                if ($resource !== 'shop' || $key !== 'items') {
                    return $key;
                }

                $message = $count === 1 ? '{count} item' : '{count} items';

                return MessageFormatter::format($message, $parameters);
            }
        };

        self::assertSame('Hello Ada', $translator->translate('shop', 'hello', ['name' => 'Ada']));
        self::assertSame('1 item', $translator->translatePlural('shop', 'items', 1, ['count' => 1]));
        self::assertSame('2 items', $translator->translatePlural('shop', 'items', 2, ['count' => 2]));
    }
}
