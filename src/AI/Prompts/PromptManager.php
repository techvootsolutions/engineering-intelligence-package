<?php

namespace Dev\EipAgent\AI\Prompts;

class PromptManager
{
    private array $prompts = [
        'architecture_v1' => "Analyze these Laravel architectural risks.\nFocus on:\n- maintainability\n- SRP violations\n- refactoring priorities\n\nContext:",
        'performance_v1'  => "Analyze these Laravel performance risks.\nFocus on:\n- query optimization\n- eager loading\n- scalability\n\nContext:",
        'security_v1'     => "Analyze these Laravel security risks.\nFocus on:\n- injection vectors\n- authentication/authorization\n- data exposure\n\nContext:",
        'default_v1'      => "Analyze these engineering risks.\nFocus on:\n- severity\n- recommended fixes\n- potential impact\n\nContext:",
    ];

    public function getPrompt(string $chunkType, string $version = 'v1'): string
    {
        $key = "{$chunkType}_{$version}";
        
        return $this->prompts[$key] ?? $this->prompts["default_{$version}"] ?? $this->prompts['default_v1'];
    }

    public function buildFullPrompt(string $chunkType, array $payload, string $version = 'v1'): string
    {
        $systemPrompt = $this->getPrompt($chunkType, $version);
        $jsonPayload = json_encode($payload, JSON_PRETTY_PRINT);
        
        return "{$systemPrompt}\n\n```json\n{$jsonPayload}\n```";
    }
}
