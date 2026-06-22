<?php
namespace Tests\Unit;

use Dev\EipAgent\AI\AIManager;
use Dev\EipAgent\AI\GeminiProvider;
use Dev\EipAgent\AI\OpenAIProvider;
use Dev\EipAgent\Exceptions\AIConfigurationException;
use Dev\EipAgent\Services\AIConfigurationValidator;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AIManagerTest extends TestCase
{
    public function test_it_resolves_openai_provider()
    {
        Config::set('eip.ai_enabled', true);
        Config::set('eip.provider', 'openai');
        Config::set('eip.providers.openai.api_key', 'test-key');

        $validator = new AIConfigurationValidator();
        $manager = new AIManager($validator);

        $provider = $manager->provider();

        $this->assertInstanceOf(OpenAIProvider::class, $provider);
    }

    public function test_it_resolves_gemini_provider()
    {
        Config::set('eip.ai_enabled', true);
        Config::set('eip.provider', 'gemini');
        Config::set('eip.providers.gemini.api_key', 'test-key');

        $validator = new AIConfigurationValidator();
        $manager = new AIManager($validator);

        $provider = $manager->provider();

        $this->assertInstanceOf(GeminiProvider::class, $provider);
    }

    public function test_it_throws_exception_if_api_key_missing()
    {
        Config::set('eip.ai_enabled', true);
        Config::set('eip.provider', 'openai');
        Config::set('eip.providers.openai.api_key', null);

        $validator = new AIConfigurationValidator();
        $manager = new AIManager($validator);

        $this->expectException(AIConfigurationException::class);
        $this->expectExceptionMessage('OpenAI API key is missing');

        $manager->provider();
    }
}
