<?php

declare(strict_types=1);

namespace WilyCode\Translation\LocaleFormatter;

use RuntimeException;

final class LocaleFormatterFactory
{
    public function __construct(
        private readonly string $resourcesDir,
        private readonly string $defaultCode = 'en-US'
    ) {
        if (!is_dir($this->resourcesDir)) {
            throw new RuntimeException("Locale formatter resources directory does not exist: {$this->resourcesDir}");
        }
    }

    public function forCode(?string $code): LocaleFormatter
    {
        $normalized = $this->normalizeCode($code);
        $default    = $this->normalizeCode($this->defaultCode);

        foreach ($this->candidateCodes($normalized, $default) as $candidate) {
            $file = rtrim($this->resourcesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $candidate . '.php';

            if (is_file($file)) {
                /** @var array<string, mixed> $config */
                $config = require $file;

                return new LocaleFormatter($config);
            }
        }

        throw new RuntimeException("Cannot find locale formatter resource for '{$code}' or default '{$this->defaultCode}'.");
    }

    private function normalizeCode(?string $code): string
    {
        $code = strtolower(trim((string) $code));
        $code = str_replace('_', '-', $code);

        // Locale codes are configuration identifiers, never filesystem paths.
        // Invalid input safely falls back to the configured default.
        return preg_match('/^[a-z]{2,8}(?:-[a-z0-9]{1,8})*$/D', $code) === 1
            ? $code
            : '';
    }

    /**
     * @return list<string>
     */
    private function candidateCodes(string $code, string $default): array
    {
        $candidates = [];

        if ($code !== '') {
            $candidates[] = $code;

            $base = explode('-', $code, 2)[0];
            if ($base !== '' && $base !== $code) {
                $candidates[] = $base;
            }
        }

        if ($default !== '') {
            $candidates[] = $default;
        }

        return array_values(array_unique($candidates));
    }
}
