<?php

declare(strict_types=1);

namespace WilyCode\Translation;

interface LanguageInterface
{
    /**
     * ISO language code, e.g. "en" or "pl".
     */
    public function getCode(): string;

    /**
     * Number of plural forms used by the language.
     */
    public function getNumberOfPluralForms(): int;

    /**
     * Returns plural form index for given count.
     *
     * The returned value should be in range 0..(getNumberOfPluralForms()-1).
     */
    public function getPluralForm(int $count): int;
}
