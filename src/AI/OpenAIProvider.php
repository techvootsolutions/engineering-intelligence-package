<?php
namespace Dev\EipAgent\AI;

use Dev\EipAgent\DTOs\ScanResult;
use Dev\EipAgent\Exceptions\AIProviderException;
use Dev\EipAgent\Services\PromptBuilder;
use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProviderInterface
{
    public function __construct(
        private PromptBuilder $promptBuilder
    ) {}

    /**
     * Send compressed AI context to OpenAI and return the analysis text.
     */
    public function analyzeContext(array $context): string
    {
        $prompt  = $this->promptBuilder->buildFromContext($context);
        $apiKey  = config('eip.providers.openai.api_key');
        $model   = config('eip.models.openai', 'gpt-4o');
        $timeout = config('eip.timeout', 60);

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a Senior Laravel Architect performing an engineering intelligence audit. Be concise and actionable.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                if ($response->status() === 429) {
                    throw new AIProviderException("OpenAI Rate Limit Exceeded: You have exceeded your API quota. Please check your billing details or try again later.");
                }
                $errorData    = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                throw new AIProviderException("OpenAI API error: " . $errorMessage);
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';

            if (empty($text)) {
                throw new AIProviderException("OpenAI returned an empty response.");
            }

            return $text;
        } catch (\Exception $e) {
            throw new AIProviderException("Failed to communicate with OpenAI: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Analyze prompt using AI model.
     */
    public function analyze(string $prompt): string
    {
        $apiKey  = config('eip.providers.openai.api_key');
        $model   = config('eip.models.openai', 'gpt-4o');
        $timeout = config('eip.timeout', 60);

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);

            if ($response->failed()) {
                if ($response->status() === 429) {
                    throw new AIProviderException("OpenAI Rate Limit Exceeded: You have exceeded your API quota. Please check your billing details or try again later.");
                }
                $errorData    = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                throw new AIProviderException("OpenAI API error: " . $errorMessage);
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';

            if (empty($text)) {
                throw new AIProviderException("OpenAI returned an empty response.");
            }

            return $text;
        } catch (\Exception $e) {
            throw new AIProviderException("Failed to communicate with OpenAI: " . $e->getMessage(), 0, $e);
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