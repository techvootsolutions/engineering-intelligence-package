<?php

return [

    'ai_enabled' => env('EIP_AI_ENABLED', false),

    'provider' => env('EIP_AI_PROVIDER', 'gemini'),

    'timeout' => env('EIP_AI_TIMEOUT', 60),

    'models' => [
        'gemini' => env('GEMINI_MODEL', 'gemini-2.5-pro'),
        'openai' => env('OPENAI_MODEL', 'gpt-5'),
        'openrouter' => env('EIP_OPENROUTER_MODEL', 'gpt-4'),
        'mistral' => env('EIP_MISTRAL_MODEL', 'codestral-latest'),
    ],

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
        ],
        'openrouter' => [
            'api_key' => env('EIP_OPENROUTER_API_KEY'),    
        ],
        'mistral' => [
            'api_key' => env('MISTRAL_API_KEY', env('EIP_MISTRAL_API_KEY')),
        ],
    ],
];