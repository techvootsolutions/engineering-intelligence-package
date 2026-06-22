<?php
namespace Dev\EipAgent\AI;

use Dev\EipAgent\DTOs\ScanResult;
use Dev\EipAgent\Exceptions\AIProviderException;
use Dev\EipAgent\Services\PromptBuilder;

use function Laravel\Ai\agent;

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
        $prompt = $this->promptBuilder->buildFromContext($context);

        try {
            $response = agent(
                'You are a Senior Laravel Architect performing an engineering intelligence audit. Be concise and actionable.'
            )->prompt($prompt, provider: 'openrouter');

            return (string) $response;
        } catch (\Exception $e) {
            throw new AIProviderException('Failed to communicate with OpenRouter: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Analyze prompt using AI model.
     */
    public function analyze(string $prompt): string
    {
        try {
            $response = agent(
                'You are a senior Laravel architecture auditor.'
            )->prompt($prompt, provider: 'openrouter');

            return (string) $response;
        } catch (\Exception $e) {
            throw new AIProviderException('Failed to communicate with OpenRouter: ' . $e->getMessage(), 0, $e);
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