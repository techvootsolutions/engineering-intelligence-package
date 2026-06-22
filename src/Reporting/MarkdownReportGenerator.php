<?php
namespace Dev\EipAgent\Reporting;

use Dev\EipAgent\Contracts\ReportExporterInterface;
use Dev\EipAgent\DTOs\ScanResult;

class MarkdownReportGenerator implements ReportExporterInterface
{
    private const SEVERITY_ORDER = ['critical', 'high', 'warning', 'info'];

    public function export(ScanResult $result, string $mode = 'summary'): string
    {
        $healthScore = $result->health['health_score'] ?? 0;
        $grade       = $this->getGrade($healthScore);
        $totalIssues = count($result->issues);

        $criticalCount = count(array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'critical'));
        $highCount     = count(array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'high'));
        $warningCount  = count(array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'warning'));
        $infoCount     = count(array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'info'));

        $md  = "# EIP Analysis Report\n\n";
        $md .= "> Generated: " . now()->toDateTimeString() . "\n\n";

        // ── Project Health ────────────────────────────────────────────────
        $md .= "## Project Health\n\n";
        $md .= "| Metric | Value |\n";
        $md .= "|--------|-------|\n";
        $md .= "| Health Score | **{$healthScore}** |\n";
        $md .= "| Grade | **{$grade}** |\n";
        $md .= "| Total Issues | {$totalIssues} |\n";
        $md .= "| Critical | {$criticalCount} |\n";
        $md .= "| High | {$highCount} |\n";
        $md .= "| Warnings | {$warningCount} |\n";
        $md .= "| Info | {$infoCount} |\n\n";

        // ── Risk Hotspots (top 5) ─────────────────────────────────────────
        if (!empty($result->hotspots)) {
            $md .= "## Risk Hotspots\n\n";
            $md .= "| File | Risk Score | Issues | Critical |\n";
            $md .= "|------|-----------|--------|----------|\n";
            foreach (array_slice($result->hotspots, 0, 5) as $h) {
                $shortFile = basename($h['file']);
                $md .= "| `{$shortFile}` | {$h['risk_score']} | {$h['issue_count']} | {$h['critical_count']} |\n";
            }
            $md .= "\n";
        }

        // ── Issues grouped by type ────────────────────────────────────────
        if (!empty($result->groupedIssues)) {
            $md .= "## Issues by Type\n\n";
            $md .= $this->formatGroupedIssues($result->groupedIssues);
        } else {
            // Fallback: flat critical list
            $md .= "## Critical Issues\n\n";
            $md .= $this->formatFlatCriticalIssues($result->issues);
        }

        // ── AI Insights ───────────────────────────────────────────────────
        if ($result->aiReport) {
            $md .= "---\n\n## AI Insights\n\n";
            $md .= $result->aiReport . "\n\n";
        }

        return $md;
    }

    // -----------------------------------------------------------------------
    // Grouped renderer
    // -----------------------------------------------------------------------

    private function formatGroupedIssues(array $groupedIssues): string
    {
        $md = '';

        foreach ($groupedIssues as $key => $data) {
            // New type-aggregated structure: {count, severity, files, sample_methods, issue_ids}
            if (isset($data['count'])) {
                $typeLabel  = ucwords(str_replace('_', ' ', $key));
                $sevEmoji   = match (strtolower($data['severity'] ?? 'info')) {
                    'critical' => '🔴',
                    'high'     => '🟠',
                    'warning'  => '🟡',
                    default    => '🔵',
                };

                $md .= "### {$sevEmoji} {$typeLabel}\n\n";
                $md .= "> **Count:** {$data['count']}  \n";
                $md .= "> **Severity:** {$data['severity']}  \n";

                if (!empty($data['files'])) {
                    $fileList = implode(', ', array_map(fn($f) => '`' . basename($f) . '`', $data['files']));
                    $md .= "> **Affected Files:** {$fileList}  \n";
                }
                if (!empty($data['sample_methods'])) {
                    $methods = implode(', ', array_map(fn($m) => "`{$m}()`", $data['sample_methods']));
                    $md .= "> **Sample Methods:** {$methods}  \n";
                }

                $md .= "\n---\n\n";
            } else {
                // Legacy file-grouped fallback: {file => [{issue}, ...]}
                $summary     = $data['summary'] ?? null;
                $fileIssues  = $data['issues'] ?? $data;

                $md .= "### `{$key}`\n\n";

                if ($summary) {
                    $md .= "> **Issues:** {$summary['total']}";
                    if ($summary['critical'] > 0) $md .= " &nbsp;|&nbsp; 🔴 Critical: {$summary['critical']}";
                    if ($summary['high'] > 0)     $md .= " &nbsp;|&nbsp; 🟠 High: {$summary['high']}";
                    if ($summary['warning'] > 0)  $md .= " &nbsp;|&nbsp; 🟡 Warning: {$summary['warning']}";
                    if ($summary['info'] > 0)     $md .= " &nbsp;|&nbsp; 🔵 Info: {$summary['info']}";
                    $md .= "\n\n";
                }

                foreach ($fileIssues as $issue) {
                    $md .= $this->formatSingleIssue($issue);
                }

                $md .= "---\n\n";
            }
        }

        return $md;
    }

    private function formatSingleIssue(array $issue): string
    {
        $typeLabel = ucwords(str_replace('_', ' ', $issue['type'] ?? 'unknown'));
        $sev       = strtolower($issue['severity'] ?? 'info');
        $sevEmoji  = match ($sev) {
            'critical' => '🔴',
            'high'     => '🟠',
            'warning'  => '🟡',
            default    => '🔵',
        };

        $md  = "#### {$sevEmoji} {$typeLabel}";
        if (!empty($issue['id'])) {
            $md .= " `{$issue['id']}`";
        }
        $md .= "\n\n";

        if (!empty($issue['line']) && $issue['line'] > 0) {
            $md .= "**Line:** {$issue['line']}  \n";
        }
        if (!empty($issue['method'])) {
            $md .= "**Method:** `{$issue['method']}()`  \n";
        }
        if (!empty($issue['message'])) {
            $md .= "**Issue:** {$issue['message']}  \n";
        }
        if (!empty($issue['impact'])) {
            $md .= "**Impact:** {$issue['impact']}  \n";
        }
        if (!empty($issue['recommendation'])) {
            $md .= "\n> 💡 {$issue['recommendation']}\n";
        }

        $md .= "\n";
        return $md;
    }

    // -----------------------------------------------------------------------
    // Flat fallback
    // -----------------------------------------------------------------------

    private function formatFlatCriticalIssues(array $issues): string
    {
        $critical = array_filter($issues, fn ($i) => ($i['severity'] ?? '') === 'critical');

        if (empty($critical)) {
            return "> ✅ No critical issues found.\n\n";
        }

        $md = '';
        foreach ($critical as $issue) {
            $file = $issue['file'] ?? 'General';
            $md  .= "#### `{$file}`\n\n";
            $md  .= $this->formatSingleIssue($issue);
        }

        return $md;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function getGrade(int $score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }
}
