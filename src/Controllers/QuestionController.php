<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Skill;
use App\Services\QuestionService;

/** Questions et entraide (F-201 à F-205). */
final class QuestionController
{
    public function index(): void
    {
        $keyword = Request::query('q');
        $tag = Request::queryInt('tag');
        View::render('questions/index', [
            'title' => 'Questions',
            'keyword' => $keyword,
            'tag' => $tag,
            'catalogue' => (new Skill())->all(),
            'result' => (new Question())->search($keyword, $tag, max(1, Request::queryInt('page'))),
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->renderForm([], ['titre' => '', 'contenu' => '', 'tags' => []]);
    }

    public function store(): void
    {
        $user = Auth::requireLogin();
        $old = ['titre' => Request::str('titre'), 'contenu' => Request::str('contenu'), 'tags' => Request::ids('tags')];
        $result = (new QuestionService())->ask($user['id'], $old['titre'], $old['contenu'], $old['tags']);

        if ($result['errors'] !== []) {
            $this->renderForm($result['errors'], $old, 422);
            return;
        }
        Session::flash('success', 'Question publiée.');
        View::redirect('/questions/' . $result['id']);
    }

    public function show(int $id): void
    {
        $question = (new Question())->find($id);
        if ($question === null) {
            View::error(404, 'Cette question n\'existe pas.');
            return;
        }
        $this->renderShow($question, null);
    }

    public function answer(int $id): void
    {
        $user = Auth::requireLogin();
        $question = (new Question())->find($id);
        if ($question === null) {
            View::error(404, 'Cette question n\'existe pas.');
            return;
        }
        $contenu = Request::str('contenu');
        $error = (new QuestionService())->answer($question, $user, $contenu);

        if ($error !== null) {
            $this->renderShow($question, $error, $contenu, 422);
            return;
        }
        Session::flash('success', 'Réponse publiée.');
        View::redirect('/questions/' . $id . '#reponses');
    }

    /** F-204 : un vote par membre et par réponse, jamais sur sa propre réponse. */
    public function vote(int $id): void
    {
        $user = Auth::requireLogin();
        $answer = (new Answer())->findWithQuestion($id);
        if ($answer === null) {
            View::error(404, 'Cette réponse n\'existe pas.');
            return;
        }
        if ((int) $answer['user_id'] === $user['id']) {
            Session::flash('error', 'Vous ne pouvez pas voter pour votre propre réponse.');
        } else {
            (new Answer())->toggleVote($id, $user['id']);
        }
        View::redirect('/questions/' . $answer['question_id'] . '#reponse-' . $id);
    }

    /** F-205 : seul l'auteur de la question accepte une réponse. */
    public function accept(int $id): void
    {
        $user = Auth::requireLogin();
        $answer = (new Answer())->findWithQuestion($id);
        if ($answer === null) {
            View::error(404, 'Cette réponse n\'existe pas.');
            return;
        }
        if ((int) $answer['question_user_id'] !== $user['id']) {
            View::error(403, 'Seul l\'auteur de la question peut accepter une réponse.');
            return;
        }
        (new Answer())->accept($id, (int) $answer['question_id']);
        Session::flash('success', 'Réponse acceptée.');
        View::redirect('/questions/' . $answer['question_id'] . '#reponse-' . $id);
    }

    private function renderForm(array $errors, array $old, int $status = 200): void
    {
        View::render('questions/create', [
            'title' => 'Poser une question',
            'errors' => $errors,
            'old' => $old,
            'catalogue' => (new Skill())->all(),
        ], $status);
    }

    private function renderShow(array $question, ?string $error, string $draft = '', int $status = 200): void
    {
        View::render('questions/show', [
            'title' => $question['titre'],
            'question' => $question,
            'answers' => (new Answer())->forQuestion($question['id'], Auth::id()),
            'error' => $error,
            'draft' => $draft,
            'me' => Auth::id(),
        ], $status);
    }
}
