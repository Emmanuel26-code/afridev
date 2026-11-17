<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\User;

final class AuthService
{
    private const MAX_EMAIL_FAILURES = 5;
    private const MAX_IP_FAILURES = 20;
    private const WINDOW_MINUTES = 15;
    private const GENERIC_ERROR = 'Email ou mot de passe incorrect.';
    /** Hash factice : même temps de calcul quand l'email n'existe pas. */
    private const DUMMY_HASH = '$2y$10$w4417xUxv6/WbfuY3Z/MsOc1PBKu4sSLuVjXdHwwKICF8rLVq/oHW';

    /**
     * F-101 : inscription.
     * @return array{errors: array<string,string>, id: ?int}
     */
    public function register(string $nom, string $pseudo, string $email, string $password, string $confirmation): array
    {
        $users = new User();
        $email = mb_strtolower($email);
        $errors = [];

        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom doit contenir entre 2 et 100 caractères.';
        }
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $pseudo)) {
            $errors['pseudo'] = 'Le pseudo doit contenir 3 à 50 caractères : lettres, chiffres, « _ », « . » ou « - ».';
        } elseif ($users->pseudoTaken($pseudo)) {
            $errors['pseudo'] = 'Ce pseudo est déjà utilisé.';
        }
        if (strlen($email) > 150 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Adresse email invalide.';
        } elseif ($users->emailTaken($email)) {
            $errors['email'] = 'Un compte existe déjà avec cet email.';
        }
        if (strlen($password) < 8 || strlen($password) > 72) {
            $errors['password'] = 'Le mot de passe doit contenir entre 8 et 72 caractères.';
        } elseif (!hash_equals($password, $confirmation)) {
            $errors['password_confirmation'] = 'Les deux mots de passe ne correspondent pas.';
        }

        if ($errors !== []) {
            return ['errors' => $errors, 'id' => null];
        }

        try {
            $id = $users->create($nom, $pseudo, $email, password_hash($password, PASSWORD_DEFAULT));
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') { // doublon créé entre la vérification et l'insertion
                return ['errors' => ['email' => 'Un compte existe déjà avec cet email ou ce pseudo.'], 'id' => null];
            }
            throw $e;
        }
        return ['errors' => [], 'id' => $id];
    }

    /**
     * F-102 : connexion avec message générique et blocage temporaire.
     * @return array{error: ?string, id: ?int}
     */
    public function attempt(string $email, string $password, string $ip): array
    {
        $email = mb_strtolower($email);
        $attempts = new LoginAttempt();

        if ($attempts->failuresForEmail($email, self::WINDOW_MINUTES) >= self::MAX_EMAIL_FAILURES
            || $attempts->failuresForIp($ip, self::WINDOW_MINUTES) >= self::MAX_IP_FAILURES) {
            return ['error' => 'Trop de tentatives de connexion. Réessayez dans ' . self::WINDOW_MINUTES . ' minutes.', 'id' => null];
        }

        $users = new User();
        $user = $users->findForLogin($email);
        $valid = password_verify($password, $user['mot_de_passe_hash'] ?? self::DUMMY_HASH) && $user !== null;

        if (!$valid) {
            $attempts->record($email, $ip);
            return ['error' => self::GENERIC_ERROR, 'id' => null];
        }
        if ($user['est_bloque']) {
            return ['error' => 'Ce compte a été suspendu par un administrateur.', 'id' => null];
        }

        $attempts->clear($email);
        if (password_needs_rehash($user['mot_de_passe_hash'], PASSWORD_DEFAULT)) {
            $users->updatePasswordHash($user['id'], password_hash($password, PASSWORD_DEFAULT));
        }
        return ['error' => null, 'id' => $user['id']];
    }
}
