<?php
declare(strict_types=1);

use App\Core\Env;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;

define('ROOT', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = ROOT . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
require ROOT . '/src/helpers.php';

Env::load(ROOT . '/.env');

// Erreurs : jamais affichées en production, toujours journalisées hors du dossier public.
error_reporting(E_ALL);
ini_set('display_errors', Env::isProduction() ? '0' : '1');
ini_set('log_errors', '1');
ini_set('error_log', ROOT . '/storage/logs/php-error.log');
set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    if (!Env::isProduction()) {
        http_response_code(500);
        echo '<pre>' . View::e((string) $e) . '</pre>';
        return;
    }
    View::error(500, 'Une erreur est survenue. Réessayez plus tard.');
});

// En-têtes de sécurité.
header_remove('X-Powered-By');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; "
    . "style-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net; "
    . "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if (Request::isHttps()) {
    header('Strict-Transport-Security: max-age=31536000');
}

Session::start();
