<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Search;

/** Besoins de collaboration CODEV (tables collab_needs et need_skills). */
final class Need
{
    public const STATUTS = ['ouvert' => 'Ouvert', 'en_cours' => 'En cours', 'pourvu' => 'Pourvu', 'clos' => 'Clos'];
    public const DISPONIBILITES = ['disponible' => 'Disponible maintenant', 'indifferent' => 'Indifférent'];

    /** @return array{items: array, total: int, page: int, pages: int} */
    public function listOpen(int $page): array
    {
        $from = " FROM collab_needs n JOIN users u ON u.id = n.user_id WHERE n.statut = 'ouvert' AND u.est_bloque = 0";
        $total = (int) Database::pdo()->query('SELECT COUNT(*)' . $from)->fetchColumn();
        $pagination = Search::paginate($total, $page);
        $items = Database::pdo()->query(
            'SELECT n.id, n.titre, n.role_recherche, n.statut, n.created_at, u.id AS user_id, u.prenom, u.nom'
            . $from . ' ORDER BY n.created_at DESC, n.id DESC LIMIT ' . Search::PER_PAGE . ' OFFSET ' . $pagination['offset']
        )->fetchAll();

        $skills = $this->skillsFor(array_column($items, 'id'));
        foreach ($items as &$item) {
            $item['skills'] = $skills[$item['id']] ?? [];
        }
        return ['items' => $items, 'total' => $total] + $pagination;
    }

    public function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT n.*, u.prenom, u.nom, p.titre AS projet_titre
             FROM collab_needs n JOIN users u ON u.id = n.user_id LEFT JOIN projects p ON p.id = n.project_id
             WHERE n.id = ? AND u.est_bloque = 0'
        );
        $stmt->execute([$id]);
        $need = $stmt->fetch();
        if (!$need) {
            return null;
        }
        $need['skills'] = $this->skillsFor([$id])[$id] ?? [];
        return $need;
    }

    public function create(int $userId, array $data): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'INSERT INTO collab_needs (user_id, project_id, titre, description, role_recherche, niveau_min, disponibilite)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $userId, $data['project_id'], $data['titre'], $data['description'],
                $data['role'], $data['niveau'], $data['disponibilite'],
            ]);
            $id = (int) $pdo->lastInsertId();
            $insert = $pdo->prepare('INSERT INTO need_skills (need_id, skill_id, type, niveau_min) VALUES (?, ?, ?, ?)');
            foreach ($data['requises'] as $skillId) {
                $insert->execute([$id, $skillId, 'requise', $data['niveau']]);
            }
            foreach ($data['souhaitees'] as $skillId) {
                $insert->execute([$id, $skillId, 'souhaitee', 1]);
            }
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** @return array<int, array> compétences groupées par besoin */
    private function skillsFor(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::pdo()->prepare(
            "SELECT ns.need_id, s.id, s.nom, ns.type, ns.niveau_min FROM need_skills ns JOIN skills s ON s.id = ns.skill_id
             WHERE ns.need_id IN ($placeholders) ORDER BY ns.type, s.nom"
        );
        $stmt->execute(array_map('intval', $ids));
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['need_id']][] = $row;
        }
        return $grouped;
    }
}
