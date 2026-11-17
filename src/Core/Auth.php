<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/** Utilisateur connecté, rechargé à chaque requête (un compte bloqué est déconnecté). */
final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('user_id');
            if (is_int($id)) {
                $user = (new User())->findById($id);
                if ($user !== null && !$user['est_bloque']) {
                    self::$user = $user;
                } else {
                    self::logout();
                }
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::forget('_csrf');
        Session::set('user_id', $userId);
        self::$user = null;
        self::$loaded = false;
    }

    public static function logout(): void
    {
        Session::reset();
        self::$user = null;
        self::$loaded = true;
    }

    public static function requireLogin(): array
    {
        $user = self::user();
        if ($user === null) {
            Session::flash('error', 'Connectez-vous pour accéder à cette page.');
            View::redirect('/auth/login');
        }
        return $user;
    }
}
