<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[1-9]\d{0,9})', $path) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        $pathExists = false;
        foreach ($this->routes as [$routeMethod, $regex, $handler]) {
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            $pathExists = true;
            if ($routeMethod !== $method) {
                continue;
            }
            if ($method === 'POST' && !Csrf::verify($_POST['_csrf'] ?? null)) {
                View::error(403, 'Votre session a expiré. Rechargez la page et réessayez.');
                return;
            }
            $params = array_map('intval', array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            [$class, $action] = $handler;
            (new $class())->$action(...$params);
            return;
        }

        if ($pathExists) {
            header('Allow: GET, POST');
            View::error(405, 'Méthode non autorisée.');
        } else {
            View::error(404, 'Page introuvable.');
        }
    }
}
