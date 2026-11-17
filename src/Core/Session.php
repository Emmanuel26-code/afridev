<?php
declare(strict_types=1);

namespace App\Core;

/** Session sécurisée : cookie HttpOnly + SameSite, Secure en HTTPS, expiration après inactivité. */
final class Session
{
    private const IDLE_SECONDS = 7200;

    public static function start(): void
    {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        session_name('afridev_sid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => Request::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        $now = time();
        if (isset($_SESSION['_last']) && $now - $_SESSION['_last'] > self::IDLE_SECONDS) {
            self::reset();
        }
        $_SESSION['_last'] = $now;
    }

    /** Nouvel identifiant (anti-fixation), données conservées. */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /** Vide la session et change l'identifiant. */
    public static function reset(): void
    {
        $_SESSION = ['_last' => time()];
        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    public static function pullFlash(string $key): ?string
    {
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }
}
