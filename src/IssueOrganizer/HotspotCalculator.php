<?php

namespace Dev\EipAgent\IssueOrganizer;

class HotspotCalculator
{
    private const SEVERITY_WEIGHTS = [
        'critical' => 4,
        'high'     => 3,
        'warning'  => 2,
        'info'     => 1,
    ];

    /**
     * Calculate per-file hotspot scores from a grouped-by-file issue map.
     *
     * @param  array<string, array> $groupedIssues  Output of IssueOrganizer::groupByFile()
     * @return array  Sorted descending by risk_score, each entry:
     *                [ file, issue_count, critical_count, high_count, warning_count, info_count, risk_score ]
     */
    public function calculate(array $groupedIssues): array
    {
        if (empty($groupedIssues)) {
            return [];
        }

        $hotspots   = [];
        $maxRawScore = 0;

        // First pass — accumulate raw scores
        foreach ($groupedIssues as $file => $issues) {
            $counts = $this->countBySeverity($issues);
            $raw    = $this->rawScore($counts);

            if ($raw > $maxRawScore) {
                $maxRawScore = $raw;
            }

            $hotspots[] = [
                'file'           => $file,
                'issue_count'    => count($issues),
                'critical_count' => $counts['critical'],
                'high_count'     => $counts['high'],
                'warning_count'  => $counts['warning'],
                'info_count'     => $counts['info'],
                '_raw'           => $raw,
            ];
        }

        // Second pass — normalise to 0–100
        foreach ($hotspots as &$h) {
            $h['risk_score'] = $maxRawScore > 0
                ? (int) round(($h['_raw'] / $maxRawScore) * 100)
                : 0;
            unset($h['_raw']);
        }
        unset($h);

        // Sort descending by risk_score
        usort($hotspots, fn ($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        return $hotspots;
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    private function countBySeverity(array $issues): array
    {
        $counts = ['critical' => 0, 'high' => 0, 'warning' => 0, 'info' => 0];

        foreach ($issues as $issue) {
            $sev = strtolower($issue['severity'] ?? 'info');
            if (array_key_exists($sev, $counts)) {
                $counts[$sev]++;
            }
        }

        return $counts;
    }

    private function rawScore(array $counts): int
    {
        $score = 0;
        foreach ($counts as $severity => $n) {
            $score += $n * (self::SEVERITY_WEIGHTS[$severity] ?? 0);
        }
        return $score;
    }
}
