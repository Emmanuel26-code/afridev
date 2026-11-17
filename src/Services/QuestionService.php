<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Answer;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Skill;

final class QuestionService
{
    private const MAX_TAGS = 5;
    private const MAX_CONTENT = 10000;

    /**
     * F-201 : titre de 10 caractères minimum, contenu, 1 à 5 tags du catalogue.
     * @param int[] $tagIds
     * @return array{errors: array<string,string>, id: ?int}
     */
    public function ask(int $userId, string $titre, string $contenu, array $tagIds): array
    {
        $errors = [];
        if (mb_strlen($titre) < 10 || mb_strlen($titre) > 255) {
            $errors['titre'] = 'Le titre doit contenir entre 10 et 255 caractères.';
        }
        if ($contenu === '' || mb_strlen($contenu) > self::MAX_CONTENT) {
            $errors['contenu'] = 'Décrivez votre question (10 000 caractères maximum).';
        }
        if (count($tagIds) < 1 || count($tagIds) > self::MAX_TAGS) {
            $errors['tags'] = 'Choisissez entre 1 et 5 tags.';
        } elseif (count((new Skill())->existingIds($tagIds)) !== count($tagIds)) {
            $errors['tags'] = 'Un des tags choisis n\'existe pas.';
        }

        if ($errors !== []) {
            return ['errors' => $errors, 'id' => null];
        }
        return ['errors' => [], 'id' => (new Question())->create($userId, $titre, $contenu, $tagIds)];
    }

    /**
     * F-202 : réponse non vide, notification à l'auteur de la question.
     * @return ?string message d'erreur, null si publiée
     */
    public function answer(array $question, array $author, string $contenu): ?string
    {
        if ($contenu === '' || mb_strlen($contenu) > self::MAX_CONTENT) {
            return 'Votre réponse ne peut pas être vide (10 000 caractères maximum).';
        }
        $answerId = (new Answer())->create($question['id'], $author['id'], $contenu);

        if ((int) $question['user_id'] !== $author['id']) {
            (new Notification())->create(
                (int) $question['user_id'],
                'nouvelle_reponse',
                $author['prenom'] . ' ' . $author['nom'] . ' a répondu à « ' . $question['titre'] . ' »',
                '/questions/' . $question['id'] . '#reponse-' . $answerId
            );
        }
        return null;
    }
}
