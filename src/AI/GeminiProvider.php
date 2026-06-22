<?php
namespace Dev\EipAgent\AI;

use Dev\EipAgent\DTOs\ScanResult;
use Dev\EipAgent\Exceptions\AIProviderException;
use Dev\EipAgent\Services\PromptBuilder;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AIProviderInterface
{
    public function __construct(
        private PromptBuilder $promptBuilder
    ) {}

    /**
     * Send compressed AI context to Gemini and return the analysis text.
     */
    public function analyzeContext(array $context): string
    {
        $prompt  = $this->promptBuilder->buildFromContext($context);
        $apiKey  = config('eip.providers.gemini.api_key');
        $model   = config('eip.models.gemini', 'gemini-2.0-flash');
        $timeout = config('eip.timeout', 60);

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout($timeout)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                ]);

            if ($response->failed()) {
                if ($response->status() === 429) {
                    throw new AIProviderException("Gemini Rate Limit Exceeded: You have exceeded your API quota. Please check your billing details or try again later.");
                }
                $errorData    = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                throw new AIProviderException("Gemini API error: " . $errorMessage);
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                throw new AIProviderException("Gemini returned an empty response.");
            }

            return $text;
        } catch (\Exception $e) {
            throw new AIProviderException("Failed to communicate with Gemini: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Analyze prompt using AI model.
     */
    public function analyze(string $prompt): string
    {
        $apiKey  = config('eip.providers.gemini.api_key');
        $model   = config('eip.models.gemini', 'gemini-2.0-flash');
        $timeout = config('eip.timeout', 60);

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout($timeout)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                ]);

            if ($response->failed()) {
                if ($response->status() === 429) {
                    throw new AIProviderException("Gemini Rate Limit Exceeded: You have exceeded your API quota. Please check your billing details or try again later.");
                }
                $errorData    = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                throw new AIProviderException("Gemini API error: " . $errorMessage);
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                throw new AIProviderException("Gemini returned an empty response.");
            }

            return $text;
        } catch (\Exception $e) {
            throw new AIProviderException("Failed to communicate with Gemini: " . $e->getMessage(), 0, $e);
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