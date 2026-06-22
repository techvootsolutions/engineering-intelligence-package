<?php

namespace Dev\EipAgent\AI\DTOs;

class ChunkData
{
    public function __construct(
        public string $id,
        public string $type,
        public int $priority,
        public int $estimatedTokens,
        public array $payload,
        public array $sourceFiles = [],
        public string $schemaVersion = '1.0',
        public array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'priority'         => $this->priority,
            'estimated_tokens' => $this->estimatedTokens,
            'payload'          => $this->payload,
            'source_files'     => $this->sourceFiles,
            'schema_version'   => $this->schemaVersion,
            'metadata'         => $this->metadata,
        ];
    }
}
