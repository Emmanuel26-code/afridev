<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\ProfileController;
use App\Controllers\ProjectController;
use App\Controllers\QuestionController;
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

$router->get('/questions', [QuestionController::class, 'index']);
$router->post('/questions', [QuestionController::class, 'store']);
$router->get('/questions/new', [QuestionController::class, 'create']);
$router->get('/questions/{id}', [QuestionController::class, 'show']);
$router->post('/questions/{id}/answers', [QuestionController::class, 'answer']);
$router->post('/answers/{id}/vote', [QuestionController::class, 'vote']);
$router->post('/answers/{id}/accept', [QuestionController::class, 'accept']);

$router->get('/projects', [ProjectController::class, 'index']);
$router->post('/projects', [ProjectController::class, 'store']);
$router->get('/projects/new', [ProjectController::class, 'create']);
$router->get('/projects/{id}', [ProjectController::class, 'show']);
$router->get('/projects/{id}/edit', [ProjectController::class, 'edit']);
$router->post('/projects/{id}/edit', [ProjectController::class, 'update']);
$router->post('/projects/{id}/delete', [ProjectController::class, 'destroy']);

$router->dispatch(Request::method(), Request::path());
