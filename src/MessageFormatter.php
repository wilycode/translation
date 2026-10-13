<?php

declare(strict_types=1);

namespace WilyCode\Translation;

final class MessageFormatter
{
    /** @param array<string, scalar|null> $parameters */
    public static function format(string $message, array $parameters): string
    {
        if ($parameters === []) {
            return $message;
        }

        $replacements = [];
        foreach ($parameters as $name => $value) {
            $replacements['{' . $name . '}'] = (string) $value;
        }

        return strtr($message, $replacements);
    }
}
