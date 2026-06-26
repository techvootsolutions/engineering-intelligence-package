<?php

namespace Techvoot\EIP\DTOs;

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
        public readonly int $line = 0,
        public readonly string $title = '',
        public readonly string $confidence = 'medium',
        public readonly string $rule_type = 'heuristic',
        public readonly string $category = 'quality',
        public readonly string $note = '',
        public readonly string $classification = 'manual_review_required',
        public readonly string $version = '1.0'
    ) {
        // Stable, deterministic issue ID — same finding always gets the same ID.
        $this->id = 'EIP-' . strtoupper(substr(md5($type . $file . $line . $message . json_encode($extra)), 0, 8));
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'title'          => $this->title,
            'severity'       => $this->severity,
            'confidence'     => $this->confidence,
            'rule_type'      => $this->rule_type,
            'category'       => $this->category,
            'classification' => $this->classification,
            'version'        => $this->version,
            'file'           => $this->file,
            'line'           => $this->line,
            'method'         => $this->method,
            'message'        => $this->message,
            'impact'         => $this->impact,
            'recommendation' => $this->recommendation,
            'note'           => $this->note,
            'score'          => $this->score,
            'extra'          => $this->extra,
        ];
    }
}
