<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Need;
use App\Models\Skill;
use App\Core\Database;

/** F-401 : validation d'un besoin de collaboration. */
final class NeedService
{
    private const MAX_SKILLS = 10;

    /** Étape 1 : titre et description, avant l'analyse IA. @return array<string,string> */
    public function validateText(string $titre, string $description): array
    {
        $errors = [];
        if (mb_strlen($titre) < 5 || mb_strlen($titre) > 150) {
            $errors['titre'] = 'Le titre doit contenir entre 5 et 150 caractères.';
        }
        if (mb_strlen($description) < 20 || mb_strlen($description) > 2000) {
            $errors['description'] = 'Décrivez votre besoin en 20 à 2 000 caractères.';
        }
        return $errors;
    }

    /** Étape 2 : besoin complet, après relecture par l'auteur. @return array<string,string> */
    public function validate(int $userId, array $in): array
    {
        $errors = $this->validateText($in['titre'], $in['description']);
        if (mb_strlen($in['role']) < 2 || mb_strlen($in['role']) > 100) {
            $errors['role'] = 'Indiquez le rôle recherché (2 à 100 caractères).';
        }
        if ($in['niveau'] < 1 || $in['niveau'] > 4) {
            $errors['niveau'] = 'Niveau invalide.';
        }
        if (!isset(Need::DISPONIBILITES[$in['disponibilite']])) {
            $errors['disponibilite'] = 'Disponibilité invalide.';
        }
        $all = array_merge($in['requises'], $in['souhaitees']);
        if (count($in['requises']) < 1 || count($in['requises']) > self::MAX_SKILLS) {
            $errors['requises'] = 'Choisissez entre 1 et 10 compétences requises.';
        } elseif (count($in['souhaitees']) > self::MAX_SKILLS) {
            $errors['souhaitees'] = '10 compétences souhaitées maximum.';
        } elseif (array_intersect($in['requises'], $in['souhaitees']) !== []) {
            $errors['souhaitees'] = 'Une compétence ne peut pas être à la fois requise et souhaitée.';
        } elseif (count((new Skill())->existingIds($all)) !== count($all)) {
            $errors['requises'] = 'Une des compétences choisies n\'existe pas.';
        }
        if ($in['project_id'] !== null && !$this->ownsProject($userId, $in['project_id'])) {
            $errors['project_id'] = 'Choisissez un de vos projets.';
        }
        return $errors;
    }

    private function ownsProject(int $userId, int $projectId): bool
    {
        $stmt = Database::pdo()->prepare('SELECT 1 FROM projects WHERE id = ? AND user_id = ?');
        $stmt->execute([$projectId, $userId]);
        return (bool) $stmt->fetchColumn();
    }
}
