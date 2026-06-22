<?php
namespace Dev\EipAgent\Services\Health;

class ComplexityCalculator
{
    public function getMultiplier(array $issue): float
    {
        $type = $issue['type'] ?? '';
        $extra = $issue['extra'] ?? [];
        $lines = $extra['lines'] ?? 0;
        
        $multiplier = 1.0;

        if ($type === 'fat_controller') {
            if ($lines > 1000) {
                $multiplier = 2.0;
            } elseif ($lines > 500) {
                $multiplier = 1.5;
            }
        } elseif ($type === 'long_method') {
            if ($lines > 200) {
                $multiplier = 2.0;
            } elseif ($lines > 100) {
                $multiplier = 1.5;
            }
        }

        return $multiplier;
    }
}
