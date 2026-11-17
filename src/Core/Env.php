<?php
declare(strict_types=1);

namespace App\Core;

/** Lit le fichier .env (hors dépôt, hors dossier public). */
final class Env
{
    private static array $vars = [];

    public static function load(string $file): void
    {
        if (!is_file($file)) {
            throw new \RuntimeException('Fichier .env introuvable.');
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $quoted = strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0];
            self::$vars[$key] = $quoted ? substr($value, 1, -1) : $value;
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::$vars[$key] ?? $default;
    }

    /** Sans APP_ENV explicite, on se comporte comme en production (le plus sûr). */
    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'production') === 'production';
    }
}
