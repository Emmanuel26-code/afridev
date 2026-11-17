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
