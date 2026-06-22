<?php

namespace Techvoot\EIP\AI;

use Techvoot\EIP\AI\AIProviderInterface;
use Techvoot\EIP\AI\GeminiProvider;
use Techvoot\EIP\AI\OpenAIProvider;
use Techvoot\EIP\AI\OpenRouterProvider;
use Techvoot\EIP\Services\AIConfigurationValidator;

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