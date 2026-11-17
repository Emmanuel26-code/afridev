<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Cache des analyses IA (table ai_analyses).
 * sujet_id : l'auteur pour une extraction de besoin, le besoin pour un matching.
 */
final class AiAnalysis
{
    public function find(string $type, int $sujetId, string $inputHash): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT resultat_json FROM ai_analyses WHERE type = ? AND sujet_id = ? AND input_hash = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$type, $sujetId, $inputHash]);
        $json = $stmt->fetchColumn();
        $data = is_string($json) ? json_decode($json, true) : null;
        return is_array($data) ? $data : null;
    }

    public function save(string $type, int $sujetId, string $inputHash, array $result, ?string $model, int $durationMs): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO ai_analyses (type, sujet_id, input_hash, resultat_json, modele, duree_ms) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $type, $sujetId, $inputHash,
            json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            $model === null ? null : mb_substr($model, 0, 100), $durationMs,
        ]);
    }

    /** Appels réels à l'IA lancés par un membre sur la dernière heure (quota : 10). */
    public function callsLastHour(int $userId): int
    {
        $stmt = Database::pdo()->prepare(
            "SELECT
               (SELECT COUNT(*) FROM ai_analyses
                 WHERE type = 'extraction_besoin' AND sujet_id = ? AND created_at > NOW() - INTERVAL 1 HOUR)
             + (SELECT COUNT(*) FROM ai_analyses a JOIN collab_needs n ON n.id = a.sujet_id
                 WHERE a.type = 'matching' AND n.user_id = ? AND a.created_at > NOW() - INTERVAL 1 HOUR)"
        );
        $stmt->execute([$userId, $userId]);
        return (int) $stmt->fetchColumn();
    }
}
