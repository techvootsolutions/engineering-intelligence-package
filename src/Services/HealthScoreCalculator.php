<?php
namespace Dev\EipAgent\Services;

class HealthScoreCalculator
{
    public function __construct(
        private \Dev\EipAgent\Services\Health\SeverityCalculator $severityCalculator,
        private \Dev\EipAgent\Services\Health\ComplexityCalculator $complexityCalculator,
        private \Dev\EipAgent\Services\Health\DensityCalculator $densityCalculator,
        private \Dev\EipAgent\Services\Health\GradeCalculator $gradeCalculator
    ) {}

    public function calculate(array $issues, int $totalFiles = 1): array
    {
        $totalFiles = max(1, $totalFiles); // prevent division by zero

        $weightedIssuesSum = 0.0;

        foreach ($issues as $issue) {
            $severityWeight = $this->severityCalculator->getWeight($issue['severity'] ?? 'info');
            $complexityMultiplier = $this->complexityCalculator->getMultiplier($issue);

            // Health formula: SeverityWeight * ComplexityMultiplier
            // We ignore the individual rule's 'score' property to avoid double-weighting.
            $weightedIssuesSum += ($severityWeight * $complexityMultiplier);
        }

        $scalingFactor = $this->densityCalculator->getScalingFactor($totalFiles);

        // HealthScore = 100 - ((WeightedIssues / TotalFiles) * ScalingFactor)
        $penalty = ($weightedIssuesSum / $totalFiles) * $scalingFactor;
        $score = max(0, 100 - $penalty);
        $scoreInt = (int) round($score);

        return [
            'health_score' => $scoreInt,
            'total_issues' => count($issues),
            'grade' => $this->gradeCalculator->getGrade($scoreInt),
            'status' => $this->gradeCalculator->getStatus($scoreInt),
            'critical' => count(
                array_filter(
                    $issues,
                    fn($issue) =>
                    ($issue['severity'] ?? '') === 'critical'
                )
            ),
            'warning' => count(
                array_filter(
                    $issues,
                    fn($issue) =>
                    ($issue['severity'] ?? '') === 'warning'
                )
            ),
        ];
    }
}
