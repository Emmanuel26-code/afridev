<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Project;
use App\Models\Skill;
use App\Services\ProjectService;

/** Partage de projets (F-301 à F-303). */
final class ProjectController
{
    public function index(): void
    {
        $keyword = Request::query('q');
        $tech = Request::queryInt('tech');
        $statut = Request::query('statut', 20);
        View::render('projects/index', [
            'title' => 'Projets',
            'keyword' => $keyword,
            'tech' => $tech,
            'statut' => isset(Project::STATUTS[$statut]) ? $statut : '',
            'catalogue' => (new Skill())->all(),
            'result' => (new Project())->search($keyword, $tech, $statut, max(1, Request::queryInt('page'))),
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->renderForm(null, [], [
            'titre' => '', 'description' => '', 'statut' => 'idee', 'lien_git' => '', 'lien_demo' => '', 'skills' => [],
        ]);
    }

    public function store(): void
    {
        $user = Auth::requireLogin();
        $in = $this->input();
        $result = (new ProjectService())->validate($in);
        if ($result['errors'] !== []) {
            $this->renderForm(null, $result['errors'], $in, 422);
            return;
        }
        $id = (new Project())->create($user['id'], $result['data']);
        Session::flash('success', 'Projet publié.');
        View::redirect('/projects/' . $id);
    }

    public function show(int $id): void
    {
        $project = (new Project())->find($id);
        if ($project === null) {
            View::error(404, 'Ce projet n\'existe pas.');
            return;
        }
        View::render('projects/show', [
            'title' => $project['titre'],
            'project' => $project,
            'isOwner' => Auth::id() === (int) $project['user_id'],
        ]);
    }

    public function edit(int $id): void
    {
        $project = $this->ownedProject($id);
        if ($project === null) {
            return;
        }
        $project['skills'] = array_column($project['skills'], 'id');
        $project['lien_git'] ??= '';
        $project['lien_demo'] ??= '';
        $this->renderForm($id, [], $project);
    }

    public function update(int $id): void
    {
        if ($this->ownedProject($id) === null) {
            return;
        }
        $in = $this->input();
        $result = (new ProjectService())->validate($in);
        if ($result['errors'] !== []) {
            $this->renderForm($id, $result['errors'], $in, 422);
            return;
        }
        (new Project())->update($id, $result['data']);
        Session::flash('success', 'Projet mis à jour.');
        View::redirect('/projects/' . $id);
    }

    public function destroy(int $id): void
    {
        if ($this->ownedProject($id) === null) {
            return;
        }
        (new Project())->delete($id);
        Session::flash('success', 'Projet supprimé.');
        View::redirect('/users/' . Auth::id());
    }

    /** F-302 : modification et suppression réservées à l'auteur, vérifiées côté serveur. */
    private function ownedProject(int $id): ?array
    {
        $user = Auth::requireLogin();
        $project = (new Project())->find($id);
        if ($project === null) {
            View::error(404, 'Ce projet n\'existe pas.');
            return null;
        }
        if ((int) $project['user_id'] !== $user['id']) {
            View::error(403, 'Vous ne pouvez modifier que vos propres projets.');
            return null;
        }
        return $project;
    }

    private function input(): array
    {
        return [
            'titre' => Request::str('titre'),
            'description' => Request::str('description'),
            'statut' => Request::str('statut'),
            'lien_git' => Request::str('lien_git'),
            'lien_demo' => Request::str('lien_demo'),
            'skills' => Request::ids('skills'),
        ];
    }

    private function renderForm(?int $id, array $errors, array $old, int $status = 200): void
    {
        View::render('projects/form', [
            'title' => $id === null ? 'Nouveau projet' : 'Modifier le projet',
            'id' => $id,
            'errors' => $errors,
            'old' => $old,
            'catalogue' => (new Skill())->all(),
        ], $status);
    }
}
