<?php
namespace Dev\EipAgent\Services\Health;

class SeverityCalculator
{
    private const WEIGHTS = [
        'info' => 1,
        'warning' => 3,
        'critical' => 8,
        'blocker' => 15,
    ];

    public function getWeight(string $severity): int
    {
        return self::WEIGHTS[strtolower($severity)] ?? 1;
    }
}
