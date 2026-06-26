<?php
namespace Techvoot\EIP\Services\Health;

use Techvoot\EIP\Rules\RuleRegistry;

class CategoryCalculator
{
    public function calculate(array $issues): array
    {
        $penalties = [
            'security' => 0,
            'architecture' => 0,
            'performance' => 0,
            'quality' => 0,
        ];

        // Group occurrences by rule type
        $issueCounts = [];
        foreach ($issues as $issue) {
            $type = $issue['type'] ?? 'unknown';
            if (!isset($issueCounts[$type])) {
                $issueCounts[$type] = 0;
            }
            $issueCounts[$type]++;
        }

        // Calculate penalty per rule type and add to category
        foreach ($issueCounts as $type => $count) {
            $ruleMeta = RuleRegistry::getRule($type);
            if ($ruleMeta) {
                if (($ruleMeta['severity'] ?? '') === 'info') {
                    continue;
                }

                $category = $ruleMeta['category'] ?? 'quality';
                $risk = $ruleMeta['risk'] ?? 1;
                $maxPenalty = $ruleMeta['max_penalty'] ?? 10;

                $rawPenalty = $count * $risk;
                $actualPenalty = min($rawPenalty, $maxPenalty);

                if (isset($penalties[$category])) {
                    $penalties[$category] += $actualPenalty;
                }
            } else {
                // Unknown rule, fallback to quality category with default low penalty
                $penalties['quality'] += min($count * 1, 10);
            }
        }

        // Calculate final scores ensuring they stay within 0-100
        return [
            'security_score' => (int) max(0, 100 - $penalties['security']),
            'architecture_score' => (int) max(0, 100 - $penalties['architecture']),
            'performance_score' => (int) max(0, 100 - $penalties['performance']),
            'quality_score' => (int) max(0, 100 - $penalties['quality']),
        ];
    }
}
