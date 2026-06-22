<?php

namespace Dev\EipAgent\AI;

use Dev\EipAgent\Exceptions\AIProviderException;
use Dev\EipAgent\Services\PromptBuilder;
use Dev\EipAgent\DTOs\ScanResult;
use Illuminate\Support\Facades\Http;


class MistralProvider implements AIProviderInterface
{
    public function __construct(
        private PromptBuilder $promptBuilder
    ) {}

    /**
     * Send compressed AI context to Mistral
     * and return the analysis text.
     */
    public function analyzeContext(array $context): string
    {
        $prompt = $this->promptBuilder
            ->buildFromContext($context);

        $apiKey = config('eip.providers.mistral.api_key') ?: env('MISTRAL_API_KEY');
        $model = config(
            'eip.models.mistral',
            'codestral-latest'
        );
        $timeout = config('eip.timeout', 60);

        try {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout($timeout)
            ->post(
                'https://api.mistral.ai/v1/chat/completions',
                [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a senior Laravel software architect.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ]
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 3000,
                ]
            );

            if ($response->failed()) {
                if ($response->status() === 429) {
                    throw new AIProviderException(
                        'Mistral Rate Limit Exceeded.'
                    );
                }

                $errorData = $response->json();
                $errorMessage =
                    $errorData['message']
                    ?? $response->body();

                throw new AIProviderException(
                    'Mistral API error: ' . $errorMessage
                );
            }

            $data = $response->json();
            $text =
                $data['choices'][0]['message']['content']
                ?? '';

            if (empty($text)) {
                throw new AIProviderException(
                    'Mistral returned empty response.'
                );
            }
            return $text;
        } catch (\Exception $e) {
            throw new AIProviderException(
                $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Analyze prompt using AI model.
     */
    public function analyze(string $prompt): string
    {
        $apiKey = config('eip.providers.mistral.api_key') ?: env('MISTRAL_API_KEY');
        $model = config(
            'eip.models.mistral',
            'codestral-latest'
        );
        $timeout = config('eip.timeout', 60);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout($timeout)
            ->post(
                'https://api.mistral.ai/v1/chat/completions',
                [
                    'model' => $model,

                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a senior Laravel software architect.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ]
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 3000,
                ]
            );

            if ($response->failed()) {
                if ($response->status() === 429) {
                    throw new AIProviderException(
                        'Mistral Rate Limit Exceeded.'
                    );
                }

                $errorData = $response->json();
                $errorMessage =
                    $errorData['message']
                    ?? $response->body();

                throw new AIProviderException(
                    'Mistral API error: ' . $errorMessage
                );
            }

            $data = $response->json();
            $text =
                $data['choices'][0]['message']['content']
                ?? '';

            if (empty($text)) {
                throw new AIProviderException(
                    'Mistral returned empty response.'
                );
            }
            return $text;
        } catch (\Exception $e) {
            throw new AIProviderException(
                $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * @deprecated Use analyzeContext() instead.
     */
    public function generateReport(ScanResult $scan): string
    {
        $prompt = $this->promptBuilder->build($scan);
        return $this->analyze($prompt);
    }
}