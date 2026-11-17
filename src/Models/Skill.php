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

    /** Catalogue avec les technologies proches (CODEV). */
    public function catalogue(): array
    {
        return Database::pdo()
            ->query('SELECT id, nom, categorie, technologies_proches FROM skills ORDER BY id')
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

    /** @param int[] $ids @return array<int, array> tags groupés par question */
    public function forQuestions(array $ids): array
    {
        return $this->grouped('question_tags', 'question_id', $ids);
    }

    /** @param int[] $ids @return array<int, array> technologies groupées par projet */
    public function forProjects(array $ids): array
    {
        return $this->grouped('project_skills', 'project_id', $ids);
    }

    /** $table et $column sont des constantes internes, jamais des saisies. */
    private function grouped(string $table, string $column, array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::pdo()->prepare(
            "SELECT l.$column AS owner_id, s.id, s.nom FROM $table l JOIN skills s ON s.id = l.skill_id
             WHERE l.$column IN ($placeholders) ORDER BY s.nom"
        );
        $stmt->execute(array_map('intval', array_values($ids)));
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['owner_id']][] = ['id' => (int) $row['id'], 'nom' => $row['nom']];
        }
        return $grouped;
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
