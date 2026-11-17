<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Notification
{
    public function create(int $userId, string $type, string $contenu, string $lien): void
    {
        $stmt = Database::pdo()->prepare('INSERT INTO notifications (user_id, type, contenu, lien) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $type, mb_substr($contenu, 0, 255), $lien]);
    }
}
