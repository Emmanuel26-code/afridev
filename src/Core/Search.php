<?php
declare(strict_types=1);

namespace App\Core;

/** Outils communs aux listes paginées avec recherche. */
final class Search
{
    public const PER_PAGE = 10;

    /** Motif LIKE sûr : %, _ et \ saisis par l'utilisateur sont pris littéralement. */
    public static function like(string $keyword): string
    {
        return '%' . addcslashes($keyword, '%_\\') . '%';
    }

    /** @return array{page:int, pages:int, offset:int} */
    public static function paginate(int $total, int $page): array
    {
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);
        return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * self::PER_PAGE];
    }
}
