<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Answer
{
    /** Réponses d'une question : acceptée d'abord, puis les plus votées. */
    public function forQuestion(int $questionId, ?int $viewerId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT a.id, a.user_id, a.contenu, a.est_acceptee, a.created_at, u.prenom, u.nom,
                    (SELECT COUNT(*) FROM answer_votes v WHERE v.answer_id = a.id) AS votes,
                    EXISTS (SELECT 1 FROM answer_votes v WHERE v.answer_id = a.id AND v.user_id = ?) AS a_vote
             FROM answers a JOIN users u ON u.id = a.user_id
             WHERE a.question_id = ? AND u.est_bloque = 0
             ORDER BY a.est_acceptee DESC, votes DESC, a.created_at ASC, a.id ASC'
        );
        $stmt->execute([$viewerId ?? 0, $questionId]);
        return $stmt->fetchAll();
    }

    /** Réponse avec l'auteur de sa question (contrôles d'accès du vote et de l'acceptation). */
    public function findWithQuestion(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT a.id, a.user_id, a.question_id, q.user_id AS question_user_id
             FROM answers a JOIN questions q ON q.id = a.question_id WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $questionId, int $userId, string $contenu): int
    {
        $stmt = Database::pdo()->prepare('INSERT INTO answers (question_id, user_id, contenu) VALUES (?, ?, ?)');
        $stmt->execute([$questionId, $userId, $contenu]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** Ajoute le vote ou le retire s'il existe déjà : jamais plus d'un vote par membre. */
    public function toggleVote(int $answerId, int $userId): void
    {
        $pdo = Database::pdo();
        $delete = $pdo->prepare('DELETE FROM answer_votes WHERE answer_id = ? AND user_id = ?');
        $delete->execute([$answerId, $userId]);
        if ($delete->rowCount() === 0) {
            $pdo->prepare('INSERT IGNORE INTO answer_votes (answer_id, user_id) VALUES (?, ?)')->execute([$answerId, $userId]);
        }
    }

    /** Une seule réponse acceptée par question ; la question passe en « résolue ». */
    public function accept(int $answerId, int $questionId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE answers SET est_acceptee = (id = ?) WHERE question_id = ?')->execute([$answerId, $questionId]);
            $pdo->prepare("UPDATE questions SET statut = 'resolue' WHERE id = ?")->execute([$questionId]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
