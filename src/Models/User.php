<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    /** Colonnes sûres : jamais le hash du mot de passe. */
    private const COLUMNS = 'id, nom, prenom, email, bio, pays_ville, disponibilite, role, est_bloque, avatar, github_url, created_at';

    public function findById(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT ' . self::COLUMNS . ' FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findForLogin(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, mot_de_passe_hash, est_bloque FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function emailTaken(string $email): bool
    {
        $stmt = Database::pdo()->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(string $nom, string $prenom, string $email, string $hash): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO users (nom, prenom, email, mot_de_passe_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nom, $prenom, $email, $hash]);
        return (int) Database::pdo()->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE users SET nom = ?, prenom = ?, bio = ?, pays_ville = ?, disponibilite = ?, github_url = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['nom'], $data['prenom'], $data['bio'], $data['pays_ville'],
            $data['disponibilite'], $data['github_url'], $id,
        ]);
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = Database::pdo()->prepare('UPDATE users SET mot_de_passe_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }

    /** Projets, activité et équipes affichés sur le profil public (F-105). */
    public function publicActivity(int $id): array
    {
        $pdo = Database::pdo();

        $projects = $pdo->prepare('SELECT id, titre, statut FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
        $projects->execute([$id]);

        $counts = $pdo->prepare(
            'SELECT (SELECT COUNT(*) FROM questions WHERE user_id = ?) AS questions,
                    (SELECT COUNT(*) FROM answers WHERE user_id = ?) AS reponses,
                    (SELECT COUNT(*) FROM answers WHERE user_id = ? AND est_acceptee = 1) AS acceptees'
        );
        $counts->execute([$id, $id, $id]);

        $teams = $pdo->prepare(
            'SELECT t.id, t.nom, tm.role FROM team_members tm JOIN teams t ON t.id = tm.team_id
             WHERE tm.user_id = ? ORDER BY tm.joined_at DESC'
        );
        $teams->execute([$id]);

        return [
            'projects' => $projects->fetchAll(),
            'counts' => $counts->fetch(),
            'teams' => $teams->fetchAll(),
        ];
    }
}
