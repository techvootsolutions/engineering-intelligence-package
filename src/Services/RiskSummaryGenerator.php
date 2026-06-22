<?php
namespace Dev\EipAgent\Services;

class RiskSummaryGenerator
{
    public function generate(array $issues): array
    {
        $riskScores = [];

        foreach ($issues as $issue) {
            $type = $issue['type'];
            if (!isset($riskScores[$type])) {
                $riskScores[$type] = 0;
            }
            $riskScores[$type] += $issue['score'] ?? 0;
        }

        arsort($riskScores);
        $topRisks = [];
        foreach (array_keys($riskScores) as $type) {
            $topRisks[] = $this->humanize($type);
            if (count($topRisks) >= 3) {
                break;
            }
        }

        return [
            'top_risks' => $topRisks,
        ];
    }

    private function humanize(string $type): string
    {
        return match ($type) {
            'missing_transaction' => 'Missing database transactions',
            'too_many_dependencies' => 'High controller coupling',
            'potential_n_plus_one' => 'Potential N+1 queries',
            'long_method' => 'Large controller methods',
            'fat_controller' => 'Oversized controllers',
            'missing_form_request' => 'Validation mixed with controller logic',
            default => str_replace('_', ' ', $type),
        };
    }
}
