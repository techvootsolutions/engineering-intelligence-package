<?php

namespace Dev\EipAgent\DTOs;

class Issue
{
    public readonly string $id;

    public function __construct(
        public readonly string $type,
        public readonly string $severity,
        public readonly string $file,
        public readonly string $message,
        public readonly string $impact = '',
        public readonly string $recommendation = '',
        public readonly string $method = '',
        public readonly int $score = 0,
        public readonly array $extra = [],
        public readonly int $line = 0
    ) {
        // Stable, deterministic issue ID — same finding always gets the same ID.
        $this->id = 'EIP-' . strtoupper(substr(md5($type . $file . $line), 0, 8));
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'severity'       => $this->severity,
            'file'           => $this->file,
            'line'           => $this->line,
            'method'         => $this->method,
            'message'        => $this->message,
            'impact'         => $this->impact,
            'recommendation' => $this->recommendation,
            'score'          => $this->score,
            'extra'          => $this->extra,
        ];
    }
}
