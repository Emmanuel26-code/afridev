<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Skill;
use App\Services\ProfileService;

/** Profil du membre connecté (F-103, F-104). */
final class ProfileController
{
    public function edit(): void
    {
        $user = Auth::requireLogin();
        View::render('profile/edit', ['title' => 'Modifier mon profil', 'errors' => [], 'old' => $user]);
    }

    public function update(): void
    {
        $user = Auth::requireLogin();
        $in = [
            'nom' => Request::str('nom'),
            'pseudo' => Request::str('pseudo'),
            'bio' => Request::str('bio'),
            'pays_ville' => Request::str('pays_ville'),
            'disponibilite' => Request::str('disponibilite'),
            'github_url' => Request::str('github_url'),
        ];
        $errors = (new ProfileService())->update($user['id'], $in);

        if ($errors !== []) {
            View::render('profile/edit', ['title' => 'Modifier mon profil', 'errors' => $errors, 'old' => $in], 422);
            return;
        }
        Session::flash('success', 'Profil mis à jour.');
        View::redirect('/users/' . $user['id']);
    }

    public function skills(): void
    {
        $user = Auth::requireLogin();
        $this->renderSkills($user['id'], null);
    }

    public function updateSkills(): void
    {
        $user = Auth::requireLogin();
        $error = (new ProfileService())->updateSkills($user['id'], Request::arr('skills'));

        if ($error !== null) {
            $this->renderSkills($user['id'], $error, 422);
            return;
        }
        Session::flash('success', 'Compétences enregistrées.');
        View::redirect('/users/' . $user['id']);
    }

    private function renderSkills(int $userId, ?string $error, int $status = 200): void
    {
        $skills = new Skill();
        $mine = [];
        foreach ($skills->forUser($userId) as $row) {
            $mine[$row['id']] = $row;
        }
        $catalogue = [];
        foreach ($skills->all() as $skill) {
            $catalogue[$skill['categorie'] ?? 'Autres'][] = $skill;
        }
        View::render('profile/skills', [
            'title' => 'Mes compétences',
            'catalogue' => $catalogue,
            'mine' => $mine,
            'error' => $error,
        ], $status);
    }
}
