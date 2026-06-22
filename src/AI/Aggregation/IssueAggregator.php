<?php

namespace Dev\EipAgent\AI\Aggregation;

use Dev\EipAgent\AI\DTOs\IssueData;

class IssueAggregator
{
    /**
     * @param IssueData[] $issues
     * @return array
     */
    public function aggregate(array $issues): array
    {
        $aggregated = [];

        foreach ($issues as $issue) {
            $key = implode('|', [
                $issue->type,
                $issue->severity,
                $issue->file,
                $issue->category,
            ]);

            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'type'                 => $issue->type,
                    'severity'             => $issue->severity,
                    'category'             => $issue->category,
                    'file'                 => $issue->file,
                    'occurrences'          => 0,
                    'methods'              => [],
                    'sample_message'       => $issue->message,
                    'sample_recommendation'=> $issue->recommendation,
                ];
            }

            $aggregated[$key]['occurrences']++;

            if ($issue->method && !in_array($issue->method, $aggregated[$key]['methods'])) {
                $aggregated[$key]['methods'][] = $issue->method;
            }
        }

        return array_values($aggregated);
    }
}
