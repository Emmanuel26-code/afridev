<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\ProfileController;
use App\Controllers\UserController;
use App\Core\Request;
use App\Core\Router;

require dirname(__DIR__) . '/src/bootstrap.php';

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->get('/auth/register', [AuthController::class, 'showRegister']);
$router->post('/auth/register', [AuthController::class, 'register']);
$router->get('/auth/login', [AuthController::class, 'showLogin']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->post('/auth/logout', [AuthController::class, 'logout']);

$router->get('/users/me', [ProfileController::class, 'edit']);
$router->post('/users/me', [ProfileController::class, 'update']);
$router->get('/users/me/skills', [ProfileController::class, 'skills']);
$router->post('/users/me/skills', [ProfileController::class, 'updateSkills']);
$router->get('/users/{id}', [UserController::class, 'show']);

$router->dispatch(Request::method(), Request::path());
