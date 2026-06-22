<?php

namespace Dev\EipAgent\AI\Prioritization;

class IssuePriorityResolver
{
    private array $severityScores = [
        'critical' => 100,
        'high'     => 75,
        'medium'   => 50,
        'low'      => 25,
        'info'     => 10,
    ];

    /**
     * @param array $aggregatedIssue
     * @param int $hotspotScore
     * @return int
     */
    public function resolve(array $aggregatedIssue, int $hotspotScore): int
    {
        $severityScore = $this->severityScores[$aggregatedIssue['severity']] ?? 10;
        
        $frequencyScore = min($aggregatedIssue['occurrences'] * 2, 20); // Cap frequency score
        
        $priority = $severityScore + $hotspotScore + $frequencyScore;
        
        // Let's ensure it stays within a readable scale, e.g., 0-200
        return (int) min($priority, 200);
    }
}
