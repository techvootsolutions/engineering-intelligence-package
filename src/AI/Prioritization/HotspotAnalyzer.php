<?php

namespace Dev\EipAgent\AI\Prioritization;

use Dev\EipAgent\AI\DTOs\IssueData;

class HotspotAnalyzer
{
    /**
     * Analyze issues to determine file risk scores.
     * 
     * @param IssueData[] $issues
     * @return array<string, int> Associative array of file path to risk score
     */
    public function analyze(array $issues): array
    {
        $fileScores = [];

        foreach ($issues as $issue) {
            $file = $issue->file;
            if (!isset($fileScores[$file])) {
                $fileScores[$file] = 0;
            }

            // Simple heuristic for now: weight by severity
            $severityWeight = match($issue->severity) {
                'critical' => 10,
                'high'     => 5,
                'medium'   => 3,
                'low'      => 1,
                default    => 0,
            };

            $fileScores[$file] += $severityWeight;
            
            // Add a small penalty for complexity/architecture issues
            if (in_array($issue->category, ['complexity', 'architecture'])) {
                $fileScores[$file] += 2;
            }
        }

        // Normalize scores slightly or cap them
        foreach ($fileScores as $file => $score) {
            $fileScores[$file] = min($score, 100); // Cap at 100 for now
        }

        arsort($fileScores);

        return $fileScores;
    }
}
