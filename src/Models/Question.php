<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Search;

final class Question
{
    /**
     * F-203 : recherche par mot-clé et par tag, 10 questions par page.
     * @return array{items: array, total: int, page: int, pages: int}
     */
    public function search(string $keyword, int $tagId, int $page): array
    {
        $where = ['u.est_bloque = 0'];
        $params = [];
        if ($keyword !== '') {
            $where[] = "(q.titre LIKE ? ESCAPE '\\\\' OR q.contenu LIKE ? ESCAPE '\\\\')";
            $params[] = Search::like($keyword);
            $params[] = Search::like($keyword);
        }
        if ($tagId > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM question_tags t WHERE t.question_id = q.id AND t.skill_id = ?)';
            $params[] = $tagId;
        }
        $from = ' FROM questions q JOIN users u ON u.id = q.user_id WHERE ' . implode(' AND ', $where);

        $count = Database::pdo()->prepare('SELECT COUNT(*)' . $from);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pagination = Search::paginate($total, $page);

        $stmt = Database::pdo()->prepare(
            'SELECT q.id, q.titre, q.statut, q.created_at, u.id AS user_id, u.prenom, u.nom,
                    (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) AS nb_reponses'
            . $from . ' ORDER BY q.created_at DESC, q.id DESC LIMIT ' . Search::PER_PAGE . ' OFFSET ' . $pagination['offset']
        );
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        $tags = (new Skill())->forQuestions(array_column($items, 'id'));
        foreach ($items as &$item) {
            $item['tags'] = $tags[$item['id']] ?? [];
        }

        return ['items' => $items, 'total' => $total] + $pagination;
    }

    public function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT q.id, q.user_id, q.titre, q.contenu, q.statut, q.created_at, u.prenom, u.nom
             FROM questions q JOIN users u ON u.id = q.user_id
             WHERE q.id = ? AND u.est_bloque = 0'
        );
        $stmt->execute([$id]);
        $question = $stmt->fetch();
        if (!$question) {
            return null;
        }
        $question['tags'] = (new Skill())->forQuestions([$id])[$id] ?? [];
        return $question;
    }

    /** @param int[] $tagIds */
    public function create(int $userId, string $titre, string $contenu, array $tagIds): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO questions (user_id, titre, contenu) VALUES (?, ?, ?)')
                ->execute([$userId, $titre, $contenu]);
            $id = (int) $pdo->lastInsertId();
            $insert = $pdo->prepare('INSERT INTO question_tags (question_id, skill_id) VALUES (?, ?)');
            foreach ($tagIds as $tagId) {
                $insert->execute([$id, $tagId]);
            }
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
