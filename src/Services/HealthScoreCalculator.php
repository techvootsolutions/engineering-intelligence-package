<?php
namespace Techvoot\EIP\Services;

class HealthScoreCalculator
{
    public function __construct(
        private \Techvoot\EIP\Services\Health\SeverityCalculator $severityCalculator,
        private \Techvoot\EIP\Services\Health\ComplexityCalculator $complexityCalculator,
        private \Techvoot\EIP\Services\Health\DensityCalculator $densityCalculator,
        private \Techvoot\EIP\Services\Health\GradeCalculator $gradeCalculator,
        private \Techvoot\EIP\Services\Health\CategoryCalculator $categoryCalculator
    ) {}

    public function calculate(array $issues, int $totalFiles = 1): array
    {
        $totalFiles = max(1, $totalFiles); // prevent division by zero

        $categoryScores = $this->categoryCalculator->calculate($issues);

        $overall = (
            $categoryScores['security_score'] * 0.40 +
            $categoryScores['architecture_score'] * 0.30 +
            $categoryScores['performance_score'] * 0.20 +
            $categoryScores['quality_score'] * 0.10
        );

        $scoreInt = (int) round($overall);

        return [
            'health_score' => $scoreInt,
            'security_score' => $categoryScores['security_score'],
            'architecture_score' => $categoryScores['architecture_score'],
            'performance_score' => $categoryScores['performance_score'],
            'quality_score' => $categoryScores['quality_score'],
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
