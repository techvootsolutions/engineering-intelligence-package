<?php
namespace Dev\EipAgent\Services\Health;

class GradeCalculator
{
    public function getGrade(float $score): string
    {
        return match (true) {
            $score >= 95 => 'A+',
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    public function getStatus(float $score): string
    {
        return match (true) {
            $score >= 95 => 'Excellent',
            $score >= 90 => 'Healthy',
            $score >= 80 => 'Stable',
            $score >= 70 => 'Needs Attention',
            $score >= 60 => 'Risky',
            default => 'Critical',
        };
    }
}
