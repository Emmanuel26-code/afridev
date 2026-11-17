<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Search;

final class Project
{
    public const STATUTS = ['idee' => 'Idée', 'en_cours' => 'En cours', 'termine' => 'Terminé'];

    /**
     * F-303 : recherche par mot-clé, filtre par technologie et statut.
     * @return array{items: array, total: int, page: int, pages: int}
     */
    public function search(string $keyword, int $skillId, string $statut, int $page): array
    {
        $where = ['u.est_bloque = 0'];
        $params = [];
        if ($keyword !== '') {
            $where[] = "(p.titre LIKE ? ESCAPE '\\\\' OR p.description LIKE ? ESCAPE '\\\\')";
            $params[] = Search::like($keyword);
            $params[] = Search::like($keyword);
        }
        if ($skillId > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM project_skills ps WHERE ps.project_id = p.id AND ps.skill_id = ?)';
            $params[] = $skillId;
        }
        if (isset(self::STATUTS[$statut])) {
            $where[] = 'p.statut = ?';
            $params[] = $statut;
        }
        $from = ' FROM projects p JOIN users u ON u.id = p.user_id WHERE ' . implode(' AND ', $where);

        $count = Database::pdo()->prepare('SELECT COUNT(*)' . $from);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pagination = Search::paginate($total, $page);

        $stmt = Database::pdo()->prepare(
            'SELECT p.id, p.titre, p.description, p.statut, p.created_at, u.id AS user_id, u.prenom, u.nom'
            . $from . ' ORDER BY p.created_at DESC, p.id DESC LIMIT ' . Search::PER_PAGE . ' OFFSET ' . $pagination['offset']
        );
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        $skills = (new Skill())->forProjects(array_column($items, 'id'));
        foreach ($items as &$item) {
            $item['skills'] = $skills[$item['id']] ?? [];
        }

        return ['items' => $items, 'total' => $total] + $pagination;
    }

    public function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT p.id, p.user_id, p.titre, p.description, p.statut, p.lien_git, p.lien_demo, p.created_at, u.prenom, u.nom
             FROM projects p JOIN users u ON u.id = p.user_id
             WHERE p.id = ? AND u.est_bloque = 0'
        );
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        if (!$project) {
            return null;
        }
        $project['skills'] = (new Skill())->forProjects([$id])[$id] ?? [];
        return $project;
    }

    public function create(int $userId, array $data): int
    {
        return $this->transaction(function (\PDO $pdo) use ($userId, $data): int {
            $pdo->prepare(
                'INSERT INTO projects (user_id, titre, description, statut, lien_git, lien_demo) VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([$userId, $data['titre'], $data['description'], $data['statut'], $data['lien_git'], $data['lien_demo']]);
            $id = (int) $pdo->lastInsertId();
            $this->saveSkills($pdo, $id, $data['skills']);
            return $id;
        });
    }

    public function update(int $id, array $data): void
    {
        $this->transaction(function (\PDO $pdo) use ($id, $data): int {
            $pdo->prepare(
                'UPDATE projects SET titre = ?, description = ?, statut = ?, lien_git = ?, lien_demo = ? WHERE id = ?'
            )->execute([$data['titre'], $data['description'], $data['statut'], $data['lien_git'], $data['lien_demo'], $id]);
            $pdo->prepare('DELETE FROM project_skills WHERE project_id = ?')->execute([$id]);
            $this->saveSkills($pdo, $id, $data['skills']);
            return $id;
        });
    }

    public function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
    }

    private function saveSkills(\PDO $pdo, int $projectId, array $skillIds): void
    {
        $insert = $pdo->prepare('INSERT INTO project_skills (project_id, skill_id) VALUES (?, ?)');
        foreach ($skillIds as $skillId) {
            $insert->execute([$projectId, $skillId]);
        }
    }

    private function transaction(callable $work): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $result = $work($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
