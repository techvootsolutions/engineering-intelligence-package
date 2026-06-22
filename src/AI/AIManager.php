<?php

namespace Dev\EipAgent\AI;

use Dev\EipAgent\AI\AIProviderInterface;
use Dev\EipAgent\AI\GeminiProvider;
use Dev\EipAgent\AI\OpenAIProvider;
use Dev\EipAgent\AI\OpenRouterProvider;
use Dev\EipAgent\Services\AIConfigurationValidator;

class AIManager
{
    public function __construct(
        private AIConfigurationValidator $validator
    ) {}

    public function provider(): AIProviderInterface
    {
        $this->validator->validate();

        return match (config('eip.provider')) {
            'openai' => app(OpenAIProvider::class),
            'gemini' => app(GeminiProvider::class),
            'openrouter' => app(OpenRouterProvider::class),
            'mistral' => app(MistralProvider::class),
            default => throw new \RuntimeException('Unsupported AI Provider'),
        };
    }
}