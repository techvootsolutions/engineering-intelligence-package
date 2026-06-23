<?php
namespace Techvoot\EIP\Services;

use Techvoot\EIP\Exceptions\AIConfigurationException;

class AIConfigurationValidator
{
    public function validate(): void
    {
        if (!config('eip.ai_enabled', true)) {
            return;
        }

        $provider = config('eip.provider') ?: env('EIP_AI_PROVIDER', 'gemini');

        if (!in_array($provider, ['openai', 'gemini', 'openrouter', 'mistral'])) {
            throw new AIConfigurationException("Unsupported AI provider: {$provider}");
        }

         $key = config('eip.api_key') ?: env('EIP_AI_KEY');
        if (empty($key)) {
            throw new AIConfigurationException("API key for {$provider} is missing. Please set EIP_AI_KEY in your environment.");
        }
    }
}
