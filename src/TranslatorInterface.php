<?php

declare(strict_types=1);

namespace WilyCode\Translation;

interface TranslatorInterface
{
    /** @param array<string, scalar|null> $parameters */
    public function translate(string $resource, string $key, array $parameters = []): string;

    /** @param array<string, scalar|null> $parameters */
    public function translatePlural(string $resource, string $key, int $count, array $parameters = []): string;
}
