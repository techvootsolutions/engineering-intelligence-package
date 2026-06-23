<?php
namespace Techvoot\EIP\AI;

use Techvoot\EIP\DTOs\ScanResult;
use Techvoot\EIP\Exceptions\AIProviderException;
use Techvoot\EIP\Services\PromptBuilder;
use Illuminate\Support\Facades\Http;

class OpenRouterProvider implements AIProviderInterface
{
    public function __construct(
        private PromptBuilder $promptBuilder
    ) {}

    /**
     * Send compressed AI context to OpenRouter and return the analysis text.
     */
    public function analyzeContext(array $context): string
    {
        $prompt  = $this->promptBuilder->buildFromContext($context);
        $apiKey  = config('eip.api_key') ?: env('EIP_AI_KEY');
        $model   = config('eip.models.openrouter', 'gpt-4');
        $timeout = config('eip.timeout', 60);

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'    => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a Senior Laravel Architect performing an engineering intelligence audit. Be concise and actionable.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                if ($response->status() === 429) {
                    throw new AIProviderException("OpenRouter Rate Limit Exceeded.");
                }
                $errorData    = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                throw new AIProviderException("OpenRouter API error: " . $errorMessage);
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';

            if (empty($text)) {
                throw new AIProviderException("OpenRouter returned an empty response.");
            }

            return $text;
        } catch (\Exception $e) {
            throw new AIProviderException("Failed to communicate with OpenRouter: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Analyze prompt using AI model.
     */
    public function analyze(string $prompt): string
    {
        $apiKey  = config('eip.api_key') ?: env('EIP_AI_KEY');
        $model   = config('eip.models.openrouter', 'gpt-4');
        $timeout = config('eip.timeout', 60);

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'    => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);

            if ($response->failed()) {
                if ($response->status() === 429) {
                    throw new AIProviderException("OpenRouter Rate Limit Exceeded.");
                }
                $errorData    = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                throw new AIProviderException("OpenRouter API error: " . $errorMessage);
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';

            if (empty($text)) {
                throw new AIProviderException("OpenRouter returned an empty response.");
            }

            return $text;
        } catch (\Exception $e) {
            throw new AIProviderException("Failed to communicate with OpenRouter: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @deprecated Use analyzeContext() instead.
     */
    public function generateReport(ScanResult $scan): string
    {
        $prompt  = $this->promptBuilder->build($scan);
        return $this->analyze($prompt);
    }
}