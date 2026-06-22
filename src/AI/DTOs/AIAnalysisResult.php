<?php

namespace Dev\EipAgent\AI\DTOs;

class AIAnalysisResult
{
    public function __construct(
        public string $chunkId,
        public string $provider,
        public string $model,
        public bool $success,
        public string $content,
        public int $inputTokens = 0,
        public int $outputTokens = 0,
        public float $durationMs = 0.0,
        public ?string $error = null,
        public array $metadata = []
    ) {}
}
