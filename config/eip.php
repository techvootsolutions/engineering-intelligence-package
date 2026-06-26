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

    /*
    |--------------------------------------------------------------------------
    | Architecture Pattern Configuration
    |--------------------------------------------------------------------------
    | Supported: laravel, service, repository, ddd, custom
    */
    'architecture' => [
        'pattern' => 'laravel',
    ],

    /*
    |--------------------------------------------------------------------------
    | Layer Strategies
    |--------------------------------------------------------------------------
    | Options: controller, service, policy, middleware, mixed, form_request, repository
    */
    'authorization_layer' => 'controller',
    'validation_layer' => 'form_request',
    'transaction_layer' => 'service',

    /*
    |--------------------------------------------------------------------------
    | Rule Engine Adjustments
    |--------------------------------------------------------------------------
    */
    'disabled_rules' => [
        // 'fat_controller',
    ],

    'rule_overrides' => [
        // 'fat_controller' => ['severity' => 'info'],
    ],

    'thresholds' => [
        'fat_controller_lines' => 250,
        'long_method_lines' => 80,
        'dependency_limit' => 8,
    ],
];