<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;

final class AuthController
{
    public function showRegister(): void
    {
        $this->redirectIfLoggedIn();
        View::render('auth/register', ['title' => 'Inscription', 'errors' => [], 'old' => []]);
    }

    public function register(): void
    {
        $this->redirectIfLoggedIn();
        $old = ['nom' => Request::str('nom'), 'prenom' => Request::str('prenom'), 'email' => Request::str('email')];
        $result = (new AuthService())->register(
            $old['nom'], $old['prenom'], $old['email'],
            Request::raw('password'), Request::raw('password_confirmation')
        );

        if ($result['errors'] !== []) {
            View::render('auth/register', ['title' => 'Inscription', 'errors' => $result['errors'], 'old' => $old], 422);
            return;
        }
        Auth::login($result['id']);
        Session::flash('success', 'Bienvenue sur AFRIDEV ! Ajoutez vos compétences pour être trouvé par CODEV.');
        View::redirect('/users/me/skills');
    }

    public function showLogin(): void
    {
        $this->redirectIfLoggedIn();
        View::render('auth/login', ['title' => 'Connexion', 'error' => null, 'old' => []]);
    }

    public function login(): void
    {
        $this->redirectIfLoggedIn();
        $email = Request::str('email');
        $result = (new AuthService())->attempt($email, Request::raw('password'), Request::ip());

        if ($result['error'] !== null) {
            View::render('auth/login', ['title' => 'Connexion', 'error' => $result['error'], 'old' => ['email' => $email]], 422);
            return;
        }
        Auth::login($result['id']);
        View::redirect('/users/' . $result['id']);
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'Vous êtes déconnecté.');
        View::redirect('/');
    }

    private function redirectIfLoggedIn(): void
    {
        if (Auth::user() !== null) {
            View::redirect('/users/' . Auth::id());
        }
    }
}
