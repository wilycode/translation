<?php

declare(strict_types=1);

namespace WilyCode\Translation\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WilyCode\Translation\MessageFormatter;

#[CoversClass(MessageFormatter::class)]
final class MessageFormatterTest extends TestCase
{
    public function test_replaces_named_placeholders(): void
    {
        self::assertSame(
            'Hello Ada, you have 3 items.',
            MessageFormatter::format('Hello {name}, you have {count} items.', ['name' => 'Ada', 'count' => 3]),
        );
    }

    public function test_empty_parameters_return_original_message(): void
    {
        self::assertSame('Hello {name}', MessageFormatter::format('Hello {name}', []));
    }

    public function test_missing_placeholders_are_not_removed(): void
    {
        self::assertSame('{missing} Ada', MessageFormatter::format('{missing} {name}', ['name' => 'Ada']));
    }

    public function test_scalar_and_null_values_use_php_string_casts(): void
    {
        self::assertSame(
            '1||3.5|0',
            MessageFormatter::format('{yes}|{no}|{number}|{zero}', [
                'yes' => true,
                'no' => false,
                'number' => 3.5,
                'zero' => 0,
            ]),
        );
        self::assertSame('empty:', MessageFormatter::format('empty:{value}', ['value' => null]));
    }

    public function test_overlapping_parameter_names_are_distinct(): void
    {
        self::assertSame('A AB A', MessageFormatter::format('{id} {id2} {id}', ['id' => 'A', 'id2' => 'AB']));
    }

    public function test_replacement_is_not_recursive(): void
    {
        self::assertSame('{other}', MessageFormatter::format('{name}', [
            'name' => '{other}',
            'other' => 'expanded',
        ]));
    }

    public function test_html_is_not_escaped(): void
    {
        self::assertSame('<b>Ada</b>', MessageFormatter::format('{name}', ['name' => '<b>Ada</b>']));
    }
}
