<?php

namespace Dev\EipAgent\AI\Schemas;

class ValidationResult
{
    public function __construct(
        private bool $isValid,
        private array $data,
        private array $warnings = [],
        private array $repairedFields = [],
        private array $missingFields = []
    ) {}

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function warnings(): array
    {
        return $this->warnings;
    }

    public function repairedFields(): array
    {
        return $this->repairedFields;
    }

    public function missingFields(): array
    {
        return $this->missingFields;
    }
}
