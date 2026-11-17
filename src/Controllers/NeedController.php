<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Need;
use App\Models\Project;
use App\Models\Skill;
use App\Services\NeedAnalyzer;
use App\Services\NeedService;

/** CODEV : besoins de collaboration (F-401, F-402). */
final class NeedController
{
    public function index(): void
    {
        View::render('codev/index', [
            'title' => 'CODEV',
            'result' => (new Need())->listOpen(max(1, Request::queryInt('page'))),
        ]);
    }

    /** Étape 1 : description libre. */
    public function create(): void
    {
        Auth::requireLogin();
        View::render('codev/describe', ['title' => 'Publier un besoin', 'errors' => [], 'old' => ['titre' => '', 'description' => '']]);
    }

    /** Étape 2 : l'IA propose rôle et compétences, l'auteur relit et corrige. */
    public function analyze(): void
    {
        $user = Auth::requireLogin();
        $old = ['titre' => Request::str('titre'), 'description' => Request::str('description')];
        $errors = (new NeedService())->validateText($old['titre'], $old['description']);
        if ($errors !== []) {
            View::render('codev/describe', ['title' => 'Publier un besoin', 'errors' => $errors, 'old' => $old], 422);
            return;
        }

        $analysis = (new NeedAnalyzer())->analyze($user['id'], $old['titre'], $old['description']);
        $this->renderReview($user['id'], [], $old + [
            'role' => $analysis['role'],
            'requises' => $analysis['requises'],
            'souhaitees' => $analysis['souhaitees'],
            'niveau' => $analysis['niveau'],
            'disponibilite' => 'disponible',
            'project_id' => null,
        ], $analysis);
    }

    /** Publication : rien n'est enregistré sans la validation de l'auteur. */
    public function store(): void
    {
        $user = Auth::requireLogin();
        $projectId = (int) Request::str('project_id');
        $in = [
            'titre' => Request::str('titre'),
            'description' => Request::str('description'),
            'role' => Request::str('role'),
            'niveau' => (int) Request::str('niveau'),
            'disponibilite' => Request::str('disponibilite'),
            'requises' => Request::ids('requises'),
            'souhaitees' => Request::ids('souhaitees'),
            'project_id' => $projectId > 0 ? $projectId : null,
        ];
        $errors = (new NeedService())->validate($user['id'], $in);
        if ($errors !== []) {
            $this->renderReview($user['id'], $errors, $in, null, 422);
            return;
        }
        $id = (new Need())->create($user['id'], $in);
        Session::flash('success', 'Besoin publié.');
        View::redirect('/codev/needs/' . $id);
    }

    public function show(int $id): void
    {
        $need = (new Need())->find($id);
        if ($need === null) {
            View::error(404, 'Ce besoin n\'existe pas.');
            return;
        }
        View::render('codev/show', [
            'title' => $need['titre'],
            'need' => $need,
            'isOwner' => Auth::id() === (int) $need['user_id'],
        ]);
    }

    private function renderReview(int $userId, array $errors, array $old, ?array $analysis, int $status = 200): void
    {
        View::render('codev/review', [
            'title' => 'Vérifier le besoin',
            'errors' => $errors,
            'old' => $old,
            'analysis' => $analysis,
            'catalogue' => (new Skill())->all(),
            'projects' => (new Project())->forUser($userId),
        ], $status);
    }
}
