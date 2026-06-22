<?php
namespace Dev\EipAgent\Services\Health;

class DensityCalculator
{
    public function getScalingFactor(int $totalFiles): float
    {
        if ($totalFiles <= 50) {
            // Forgiving for small apps
            return 5.0;
        }

        if ($totalFiles <= 500) {
            // Balanced
            return 10.0;
        }

        // Strict for large apps
        return 15.0;
    }
}
