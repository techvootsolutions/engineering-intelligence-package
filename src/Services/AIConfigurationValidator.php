<?php
namespace Dev\EipAgent\Services;

use Dev\EipAgent\Exceptions\AIConfigurationException;

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

        if ($provider === 'openai') {
            $key = config('eip.providers.openai.api_key') ?: env('OPENAI_API_KEY');
            if (empty($key)) {
                throw new AIConfigurationException("OpenAI API key is missing");
            }
        }

        if ($provider === 'gemini') {
            $key = config('eip.providers.gemini.api_key') ?: env('GEMINI_API_KEY');
            if (empty($key)) {
                throw new AIConfigurationException("Gemini API key is missing");
            }
        }

        if ($provider === 'openrouter') {
            $key = config('eip.providers.openrouter.api_key') ?: env('OPENROUTER_API_KEY');
            if (empty($key)) {
                throw new AIConfigurationException("OpenRouter API key is missing");
            }
        }

        if ($provider === 'mistral') {
            $key = config('eip.providers.mistral.api_key') ?: env('MISTRAL_API_KEY');
            if (empty($key)) {
                throw new AIConfigurationException("Mistral API key is missing");
            }
        }
    }
}
