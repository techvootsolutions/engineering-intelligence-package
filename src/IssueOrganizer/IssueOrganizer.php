<?php

namespace Dev\EipAgent\IssueOrganizer;

class IssueOrganizer
{
    /**
     * Severity weight map — never alphabetical.
     */
    private const SEVERITY_WEIGHTS = [
        'critical' => 4,
        'high'     => 3,
        'warning'  => 2,
        'info'     => 1,
    ];

    /**
     * Group a flat issue array by file path.
     *
     * @param  array $issues  Flat array of issue arrays (from Issue::toArray())
     * @return array<string, array> Map of file => issues[]
     */
    public function groupByFile(array $issues): array
    {
        $grouped = [];

        foreach ($issues as $issue) {
            $file = $issue['file'] ?? 'unknown';
            $grouped[$file][] = $issue;
        }

        // Sort files alphabetically for deterministic output
        ksort($grouped);

        // Sort issues within each file: severity DESC → line ASC
        foreach ($grouped as $file => &$fileIssues) {
            usort($fileIssues, fn ($a, $b) => $this->compareIssues($a, $b));
        }
        unset($fileIssues);

        return $grouped;
    }

    /**
     * Group a flat issue array by issue type and summarize them.
     *
     * @param  array $issues
     * @return array
     */
    public function groupByTypeAndSummarize(array $issues): array
    {
        $grouped = [];

        foreach ($issues as $issue) {
            $type = $issue['type'] ?? 'unknown';
            
            if (!isset($grouped[$type])) {
                $grouped[$type] = [
                    'count' => 0,
                    'severity' => $issue['severity'] ?? 'info',
                    'files' => [],
                    'sample_methods' => [],
                    'issue_ids' => [],
                ];
            }

            $grouped[$type]['count']++;
            
            if (!empty($issue['file'])) {
                $grouped[$type]['files'][] = $issue['file'];
            }
            if (!empty($issue['method'])) {
                $grouped[$type]['sample_methods'][] = $issue['method'];
            }
            if (!empty($issue['id'])) {
                $grouped[$type]['issue_ids'][] = $issue['id'];
            }
        }

        // Clean up arrays to unique values and sort
        foreach ($grouped as $type => &$data) {
            $data['files'] = array_values(array_unique($data['files']));
            $data['sample_methods'] = array_values(array_unique($data['sample_methods']));
            $data['issue_ids'] = array_values(array_unique($data['issue_ids']));
            
            // Limit sample methods to top 5
            if (count($data['sample_methods']) > 5) {
                $data['sample_methods'] = array_slice($data['sample_methods'], 0, 5);
            }
        }
        unset($data);

        // Sort by severity descending, then count descending
        uasort($grouped, function($a, $b) {
            $sevDiff = $this->severityWeight($b['severity'] ?? 'info') <=> $this->severityWeight($a['severity'] ?? 'info');
            if ($sevDiff !== 0) {
                return $sevDiff; // Descending
            }
            return ($b['count'] ?? 0) <=> ($a['count'] ?? 0); // Descending
        });

        return $grouped;
    }

    /**
     * Sort issues by: severity DESC → file ASC → line ASC.
     */
    public function sortBySeverity(array $issues): array
    {
        usort($issues, fn ($a, $b) => $this->compareIssues($a, $b, includeFile: true));
        return $issues;
    }

    /**
     * Filter issues to a specific severity level.
     */
    public function filterBySeverity(array $issues, string $severity): array
    {
        $severity = strtolower(trim($severity));
        return array_values(
            array_filter($issues, fn ($i) => strtolower($i['severity'] ?? '') === $severity)
        );
    }

    /**
     * Filter issues to a specific type slug.
     */
    public function filterByType(array $issues, string $type): array
    {
        $type = strtolower(trim($type));
        return array_values(
            array_filter($issues, fn ($i) => strtolower($i['type'] ?? '') === $type)
        );
    }

    /**
     * Filter issues to those whose file path contains the given string (partial match).
     */
    public function filterByFile(array $issues, string $file): array
    {
        $file = strtolower(trim($file));
        return array_values(
            array_filter($issues, function ($i) use ($file) {
                return str_contains(strtolower($i['file'] ?? ''), $file);
            })
        );
    }

    /**
     * Cap issues to the top N entries (applied after sorting).
     */
    public function limit(array $issues, int $n): array
    {
        return array_slice($issues, 0, max(1, $n));
    }

    /**
     * Apply all CLI filter options in the correct pipeline order:
     *  severity filter → type filter → file filter → sort → limit
     *
     * @param  array $issues   Flat issue array
     * @param  array $options  Keys: severity, type, file, limit, sort, group_by
     */
    public function applyFilters(array $issues, array $options): array
    {
        if (!empty($options['severity'])) {
            $issues = $this->filterBySeverity($issues, $options['severity']);
        }

        if (!empty($options['type'])) {
            $issues = $this->filterByType($issues, $options['type']);
        }

        if (!empty($options['file'])) {
            $issues = $this->filterByFile($issues, $options['file']);
        }

        // Default sort is by severity
        $sort = $options['sort'] ?? 'severity';
        if ($sort === 'severity') {
            $issues = $this->sortBySeverity($issues);
        } elseif ($sort === 'file') {
            usort($issues, fn ($a, $b) => strcmp($a['file'] ?? '', $b['file'] ?? ''));
        } elseif ($sort === 'line') {
            usort($issues, fn ($a, $b) => ($a['line'] ?? 0) <=> ($b['line'] ?? 0));
        }

        if (!empty($options['limit']) && (int) $options['limit'] > 0) {
            $issues = $this->limit($issues, (int) $options['limit']);
        }

        return $issues;
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    private function severityWeight(string $severity): int
    {
        return self::SEVERITY_WEIGHTS[strtolower($severity)] ?? 0;
    }

    private function compareIssues(array $a, array $b, bool $includeFile = false): int
    {
        // Severity descending
        $bySeverity = $this->severityWeight($b['severity'] ?? '')
            <=> $this->severityWeight($a['severity'] ?? '');

        if ($bySeverity !== 0) {
            return $bySeverity;
        }

        // File ascending (only relevant for global sort)
        if ($includeFile) {
            $byFile = strcmp($a['file'] ?? '', $b['file'] ?? '');
            if ($byFile !== 0) {
                return $byFile;
            }
        }

        // Line ascending
        return ($a['line'] ?? 0) <=> ($b['line'] ?? 0);
    }
}
