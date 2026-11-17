<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    /** Échappement HTML : à utiliser pour toute donnée affichée. */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        $content = self::capture($template, $data);
        echo self::capture('layout', $data + ['content' => $content]);
    }

    public static function error(int $status, string $message): void
    {
        self::render('errors/error', ['title' => 'Erreur', 'message' => $message], $status);
    }

    /** Redirection interne uniquement (chemin commençant par un seul /). */
    public static function redirect(string $path): never
    {
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            $path = '/';
        }
        header('Location: ' . $path, true, 303);
        exit;
    }

    private static function capture(string $template, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__, 2) . '/templates/' . $template . '.php';
        return (string) ob_get_clean();
    }
}
