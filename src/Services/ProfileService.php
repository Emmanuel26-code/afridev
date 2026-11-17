<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Skill;
use App\Models\User;

final class ProfileService
{
    public const DISPONIBILITES = ['disponible' => 'Disponible', 'occupe' => 'Occupé'];
    public const NIVEAUX = [1 => 'Débutant', 2 => 'Intermédiaire', 3 => 'Avancé', 4 => 'Expert'];
    private const MAX_SKILLS = 30;

    /**
     * F-103 : modification du profil.
     * @return array<string,string> erreurs par champ
     */
    public function update(int $userId, array $in): array
    {
        $errors = [];
        if (mb_strlen($in['nom']) < 2 || mb_strlen($in['nom']) > 100) {
            $errors['nom'] = 'Le nom doit contenir entre 2 et 100 caractères.';
        }
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $in['pseudo'])) {
            $errors['pseudo'] = 'Le pseudo doit contenir 3 à 50 caractères : lettres, chiffres, « _ », « . » ou « - ».';
        } elseif ((new User())->pseudoTaken($in['pseudo'], $userId)) {
            $errors['pseudo'] = 'Ce pseudo est déjà utilisé.';
        }
        if (mb_strlen($in['bio']) > 500) {
            $errors['bio'] = 'La bio ne doit pas dépasser 500 caractères.';
        }
        if (mb_strlen($in['pays_ville']) > 100) {
            $errors['pays_ville'] = '100 caractères maximum.';
        }
        if (!isset(self::DISPONIBILITES[$in['disponibilite']])) {
            $errors['disponibilite'] = 'Disponibilité invalide.';
        }
        if ($in['github_url'] !== '' && !preg_match('#^https://github\.com/[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})/?$#', $in['github_url'])) {
            $errors['github_url'] = 'Indiquez l\'adresse de votre profil, par exemple https://github.com/pseudo.';
        }

        if ($errors === []) {
            (new User())->updateProfile($userId, [
                'nom' => $in['nom'],
                'pseudo' => $in['pseudo'],
                'bio' => $in['bio'] === '' ? null : $in['bio'],
                'pays_ville' => $in['pays_ville'] === '' ? null : $in['pays_ville'],
                'disponibilite' => $in['disponibilite'],
                'github_url' => $in['github_url'] === '' ? null : rtrim($in['github_url'], '/'),
            ]);
        }
        return $errors;
    }

    /**
     * F-104 : compétences. $posted = [skill_id => ['on' => '1', 'niveau' => '1..4', 'annees' => '']].
     * @return ?string message d'erreur, null si enregistré
     */
    public function updateSkills(int $userId, array $posted): ?string
    {
        $rows = [];
        foreach ($posted as $skillId => $values) {
            if (!is_array($values) || empty($values['on'])) {
                continue;
            }
            $skillId = filter_var($skillId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $niveau = filter_var($values['niveau'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4]]);
            $anneesRaw = is_string($values['annees'] ?? null) ? trim($values['annees']) : '';
            $annees = $anneesRaw === '' ? null
                : filter_var($anneesRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 50]]);

            if ($skillId === false || $niveau === false || $annees === false) {
                return 'Vérifiez les niveaux (1 à 4) et les années d\'expérience (0 à 50).';
            }
            $rows[$skillId] = ['skill_id' => $skillId, 'niveau' => $niveau, 'annees' => $annees];
        }

        if (count($rows) > self::MAX_SKILLS) {
            return 'Vous pouvez choisir ' . self::MAX_SKILLS . ' compétences au maximum.';
        }
        $skills = new Skill();
        if (count($skills->existingIds(array_keys($rows))) !== count($rows)) {
            return 'Une compétence choisie n\'existe pas dans le catalogue.';
        }

        $skills->replaceForUser($userId, array_values($rows));
        return null;
    }
}
