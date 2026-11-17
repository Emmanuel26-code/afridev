<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;

/**
 * Appels au modèle de langage (OpenRouter, format compatible OpenAI), côté serveur uniquement.
 * Garde-fous : 15 s maximum au total, une nouvelle tentative, réponse JSON validée.
 * En cas d'échec, renvoie null : l'appelant passe en mode dégradé (F-407).
 */
final class AiClient
{
    private const TOTAL_TIMEOUT = 15.0;
    private const MIN_RETRY_TIME = 3.0;

    private ?string $lastModel = null;
    private int $lastDurationMs = 0;

    public function isConfigured(): bool
    {
        return Env::get('AI_API_URL') !== '' && Env::get('AI_API_KEY') !== '' && Env::get('AI_MODEL') !== '';
    }

    /** @return ?array le JSON décodé, ou null si l'IA est indisponible, trop lente ou hors format */
    public function json(string $system, string $user): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }
        $start = microtime(true);
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $remaining = self::TOTAL_TIMEOUT - (microtime(true) - $start);
            if ($remaining < ($attempt === 1 ? 1.0 : self::MIN_RETRY_TIME)) {
                break;
            }
            $result = $this->call($system, $user, $remaining);
            if ($result !== null) {
                $this->lastDurationMs = (int) round((microtime(true) - $start) * 1000);
                return $result;
            }
        }
        $this->lastDurationMs = (int) round((microtime(true) - $start) * 1000);
        return null;
    }

    public function lastModel(): ?string
    {
        return $this->lastModel;
    }

    public function lastDurationMs(): int
    {
        return $this->lastDurationMs;
    }

    private function call(string $system, string $user, float $timeout): ?array
    {
        $payload = json_encode([
            'model' => Env::get('AI_MODEL'),
            'temperature' => 0.2,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $ch = curl_init(Env::get('AI_API_URL'));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . Env::get('AI_API_KEY'),
                'X-Title: AFRIDEV',
            ],
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT_MS => (int) ($timeout * 1000),
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!is_string($body) || $status !== 200) {
            // Journal serveur seulement : jamais la clé, jamais affiché à l'utilisateur.
            error_log(sprintf('[AiClient] échec HTTP %d %s', $status, $error));
            return null;
        }
        $response = json_decode($body, true);
        $this->lastModel = is_array($response) && is_string($response['model'] ?? null) ? $response['model'] : null;
        $content = $response['choices'][0]['message']['content'] ?? null;
        if (!is_string($content)) {
            error_log('[AiClient] réponse sans contenu');
            return null;
        }
        return self::decode($content);
    }

    /** Accepte un JSON éventuellement entouré de ```json … ```. */
    private static function decode(string $content): ?array
    {
        $content = trim($content);
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $content, $m)) {
            $content = $m[1];
        }
        $data = json_decode($content, true);
        if (!is_array($data)) {
            error_log('[AiClient] JSON invalide reçu');
            return null;
        }
        return $data;
    }
}
