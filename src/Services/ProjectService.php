<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\Skill;

final class ProjectService
{
    private const MAX_SKILLS = 10;

    /**
     * F-301 : validation d'une fiche projet.
     * @return array{errors: array<string,string>, data: array}
     */
    public function validate(array $in): array
    {
        $errors = [];
        if (mb_strlen($in['titre']) < 3 || mb_strlen($in['titre']) > 150) {
            $errors['titre'] = 'Le titre doit contenir entre 3 et 150 caractères.';
        }
        if (mb_strlen($in['description']) < 10 || mb_strlen($in['description']) > 5000) {
            $errors['description'] = 'La description doit contenir entre 10 et 5 000 caractères.';
        }
        if (!isset(Project::STATUTS[$in['statut']])) {
            $errors['statut'] = 'Statut invalide.';
        }
        if (count($in['skills']) < 1 || count($in['skills']) > self::MAX_SKILLS) {
            $errors['skills'] = 'Choisissez entre 1 et 10 technologies.';
        } elseif (count((new Skill())->existingIds($in['skills'])) !== count($in['skills'])) {
            $errors['skills'] = 'Une des technologies choisies n\'existe pas.';
        }
        foreach (['lien_git' => 'Lien Git', 'lien_demo' => 'Lien de démo'] as $field => $label) {
            if ($in[$field] !== '' && !self::isWebUrl($in[$field])) {
                $errors[$field] = $label . ' invalide : adresse complète en https:// attendue.';
            }
        }

        $data = $in;
        $data['lien_git'] = $in['lien_git'] === '' ? null : $in['lien_git'];
        $data['lien_demo'] = $in['lien_demo'] === '' ? null : $in['lien_demo'];
        return ['errors' => $errors, 'data' => $data];
    }

    /** Seuls http et https sont acceptés : bloque javascript:, data:, etc. */
    private static function isWebUrl(string $url): bool
    {
        if (strlen($url) > 255 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true) && (string) parse_url($url, PHP_URL_HOST) !== '';
    }
}
