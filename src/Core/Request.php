<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return '/' . trim(is_string($path) ? $path : '/', '/');
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    }

    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /** Champ texte POST, nettoyé des espaces ; '' si absent, non textuel ou pas en UTF-8 valide. */
    public static function str(string $key): string
    {
        return trim(self::raw($key));
    }

    /** Champ POST brut (mots de passe : on ne retire rien). */
    public static function raw(string $key): string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) && mb_check_encoding($value, 'UTF-8') ? $value : '';
    }

    public static function arr(string $key): array
    {
        $value = $_POST[$key] ?? [];
        return is_array($value) ? $value : [];
    }
}
