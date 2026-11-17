<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Skill;
use App\Models\User;

/** F-105 : profil public (l'email n'est jamais affiché). */
final class UserController
{
    public function show(int $id): void
    {
        $user = (new User())->findById($id);
        if ($user === null || $user['est_bloque']) {
            View::error(404, 'Ce profil n\'existe pas.');
            return;
        }
        unset($user['email']);

        View::render('users/show', [
            'title' => $user['pseudo'],
            'profile' => $user,
            'skills' => (new Skill())->forUser($id),
            'activity' => (new User())->publicActivity($id),
            'isMe' => Auth::id() === $id,
        ]);
    }
}
