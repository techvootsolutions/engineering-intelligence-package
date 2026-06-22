<?php

namespace Dev\EipAgent\AI\DTOs;

class IssueData
{
    public function __construct(
        public string $type,
        public string $category,
        public string $severity,
        public string $file,
        public ?string $method,
        public string $message,
        public ?string $recommendation,
        public array $metadata = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'type'           => $this->type,
            'category'       => $this->category,
            'severity'       => $this->severity,
            'file'           => $this->file,
            'method'         => $this->method,
            'message'        => $this->message,
            'recommendation' => $this->recommendation,
            'metadata'       => $this->metadata,
        ];
    }
}
