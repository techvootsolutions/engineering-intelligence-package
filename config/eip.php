<?php

return [

    'ai_enabled' => env('EIP_AI_ENABLED', false),

    'provider' => env('EIP_AI_PROVIDER', 'gemini'),

    'timeout' => env('EIP_AI_TIMEOUT', 60),

    'models' => [
        'gemini' => env('EIP_AI_MODEL', 'gemini-2.5-pro'),
        'openai' => env('EIP_AI_MODEL', 'gpt-5'),
        'openrouter' => env('EIP_AI_MODEL', 'gpt-4'),
        'mistral' => env('EIP_AI_MODEL', 'codestral-latest'),
    ],

    'api_key' => env('EIP_AI_KEY'),
];