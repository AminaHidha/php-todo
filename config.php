<?php
declare(strict_types=1);

/**
 * Reads a setting from the .env file (KEY=value lines).
 * Returns $default if the file or the key does not exist.
 */
function env(string $key, string $default = ''): string
{
    static $vars = null;

    if ($vars === null) {
        $vars = [];
        $file = __DIR__ . '/.env';

        if (is_readable($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

            foreach ($lines as $line) {
                $line = trim($line);

                // Skip comments and lines without "="
                if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $vars[trim($name)] = trim($value);
            }
        }
    }

    return $vars[$key] ?? $default;
}