<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Skill
{
    public function all(): array
    {
        return Database::pdo()
            ->query('SELECT id, nom, categorie FROM skills ORDER BY categorie, nom')
            ->fetchAll();
    }

    /** @return int[] identifiants existants parmi ceux fournis */
    public function existingIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::pdo()->prepare("SELECT id FROM skills WHERE id IN ($placeholders)");
        $stmt->execute(array_values($ids));
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function forUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT s.id, s.nom, s.categorie, us.niveau, us.annees_experience
             FROM user_skills us JOIN skills s ON s.id = us.skill_id
             WHERE us.user_id = ? ORDER BY us.niveau DESC, s.nom'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Remplace toutes les compétences d'un membre (transaction). */
    public function replaceForUser(int $userId, array $rows): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM user_skills WHERE user_id = ?')->execute([$userId]);
            $insert = $pdo->prepare(
                'INSERT INTO user_skills (user_id, skill_id, niveau, annees_experience) VALUES (?, ?, ?, ?)'
            );
            foreach ($rows as $row) {
                $insert->execute([$userId, $row['skill_id'], $row['niveau'], $row['annees']]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
