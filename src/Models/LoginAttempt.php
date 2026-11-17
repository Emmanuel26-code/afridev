<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class LoginAttempt
{
    public function failuresForEmail(string $email, int $minutes): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE email_hash = ? AND attempted_at > NOW() - INTERVAL ? MINUTE'
        );
        $stmt->execute([self::hash($email), $minutes]);
        return (int) $stmt->fetchColumn();
    }

    public function failuresForIp(string $ip, int $minutes): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > NOW() - INTERVAL ? MINUTE'
        );
        $stmt->execute([$ip, $minutes]);
        return (int) $stmt->fetchColumn();
    }

    public function record(string $email, string $ip): void
    {
        $pdo = Database::pdo();
        $pdo->prepare('INSERT INTO login_attempts (email_hash, ip) VALUES (?, ?)')->execute([self::hash($email), $ip]);
        $pdo->exec('DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 1 DAY');
    }

    public function clear(string $email): void
    {
        Database::pdo()->prepare('DELETE FROM login_attempts WHERE email_hash = ?')->execute([self::hash($email)]);
    }

    private static function hash(string $email): string
    {
        return hash('sha256', $email);
    }
}
