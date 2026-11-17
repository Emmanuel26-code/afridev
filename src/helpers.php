<?php
declare(strict_types=1);

use App\Core\View;

/** Raccourcis pour les vues. */
function e(mixed $value): string
{
    return View::e($value);
}

function invalid(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' is-invalid' : '';
}

function field_error(array $errors, string $field): string
{
    return isset($errors[$field])
        ? '<div class="invalid-feedback">' . e($errors[$field]) . '</div>'
        : '';
}

/** Date SQL affichée en français : 17/11/2026 à 14:05. */
function date_fr(string $datetime): string
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $datetime);
    return $date ? $date->format('d/m/Y') . ' à ' . $date->format('H:i') : '';
}

/** Lien vers la page courante avec des paramètres GET modifiés. */
function url_with(string $path, array $params): string
{
    $query = http_build_query(array_filter($params, static fn ($v) => $v !== '' && $v !== 0 && $v !== null));
    return $path . ($query !== '' ? '?' . $query : '');
}
