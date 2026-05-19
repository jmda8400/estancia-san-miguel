<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GroqChatService
{
    public function generate(string $systemPrompt, string $userPrompt): ?string
    {
        $config = config('services.groq', []);

        $apiKey = $config['api_key'] ?? null;
        $model = $config['model'] ?? 'llama-3.1-8b-instant';
        $baseUrl = $config['base_url'] ?? 'https://api.groq.com/openai/v1/chat/completions';

        if (empty($apiKey) || empty($baseUrl)) {
            Log::warning('Groq no configurado correctamente.');

            return null;
        }

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt,
                ],
            ],
            'temperature' => 0.2,
            'max_tokens' => 220,
            'top_p' => 0.9,
        ];

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(20)
                ->post($baseUrl, $payload);

            if ($response->failed()) {
                Log::error('Error al llamar a Groq.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return null;
            }

            $text = data_get($response->json(), 'choices.0.message.content');

            if (! is_string($text) || trim($text) === '') {
                Log::warning('Groq respondió sin contenido útil.');

                return null;
            }

            return trim($text);
        } catch (Throwable $e) {
            Log::error('Excepción al llamar a Groq.', [
                'message' => $e->getMessage(),
                'class' => $e::class,
            ]);

            return null;
        }
    }
}
