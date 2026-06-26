<?php
namespace Techvoot\EIP\Reporting;

use Techvoot\EIP\Contracts\ReportExporterInterface;
use Techvoot\EIP\DTOs\ScanResult;

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
        $md .= "| Overall Health | **{$healthScore}** (Grade {$grade}) |\n";
        $md .= "| Security Score | **" . ($result->health['security_score'] ?? 100) . "** |\n";
        $md .= "| Architecture Score | **" . ($result->health['architecture_score'] ?? 100) . "** |\n";
        $md .= "| Performance Score | **" . ($result->health['performance_score'] ?? 100) . "** |\n";
        $md .= "| Quality Score | **" . ($result->health['quality_score'] ?? 100) . "** |\n";
        $md .= "| Total Issues | {$totalIssues} (🔴 {$criticalCount} | 🟠 {$highCount} | 🟡 {$warningCount} | 🔵 {$infoCount}) |\n\n";

        // ── Finding Breakdown ─────────────────────────────────────────────
        if (!empty($result->findingBreakdown)) {
            $md .= "## Finding Breakdown\n\n";
            $md .= "- Deterministic Findings: " . ($result->findingBreakdown['deterministic'] ?? 0) . "\n";
            $md .= "- Heuristic Findings: " . ($result->findingBreakdown['heuristic'] ?? 0) . "\n";
            $md .= "- Architectural Findings: " . ($result->findingBreakdown['architectural'] ?? 0) . "\n\n";
        }

        // ── Risk Hotspots (top 5) ─────────────────────────────────────────
        if (!empty($result->hotspots)) {
            $md .= "## Risk Hotspots\n\n";
            foreach (array_slice($result->hotspots, 0, 5) as $h) {
                $shortFile = basename($h['file']);
                $md .= "### `{$shortFile}`\n\n";
                $md .= "File has {$h['issue_count']} issues (Risk Score: {$h['risk_score']}):\n\n";
                $md .= "- Security Issues: " . ($h['categories']['security'] ?? 0) . "\n";
                $md .= "- Architecture Issues: " . ($h['categories']['architecture'] ?? 0) . "\n";
                $md .= "- Performance Issues: " . ($h['categories']['performance'] ?? 0) . "\n";
                $md .= "- Quality Issues: " . ($h['categories']['quality'] ?? 0) . "\n\n";
            }
        }

        // ── Issues grouped by type & classification ───────────────────────
        if (!empty($result->groupedIssues)) {
            $verified = [];
            $manualReview = [];

            foreach ($result->groupedIssues as $key => $data) {
                $classification = $data['classification'] ?? 'manual_review_required'; // Default to manual
                // Fallback for flat fallback which lacks classification in the group root:
                if (!isset($data['classification']) && isset($data['issues'][0]['classification'])) {
                    $classification = $data['issues'][0]['classification'];
                }

                if ($classification === 'verified') {
                    $verified[$key] = $data;
                } else {
                    $manualReview[$key] = $data;
                }
            }

            if (!empty($verified)) {
                $md .= "## High Confidence Findings\n\n";
                $md .= $this->formatGroupedIssues($verified);
            }

            if (!empty($manualReview)) {
                $md .= "## Requires Manual Review\n\n";
                $md .= $this->formatGroupedIssues($manualReview);
            }

        } else {
            // Fallback: flat critical list
            $md .= "## Critical Issues\n\n";
            $md .= $this->formatFlatCriticalIssues($result->issues);
        }

        // ── AI Insights ───────────────────────────────────────────────────
        if (!empty($result->metadata['custom_instruction'])) {
            $md .= "---\n\n## AI Analysis Configuration\n\n";
            $md .= "**Custom User Instruction:**\n";
            $md .= "> " . str_replace("\n", "\n> ", $result->metadata['custom_instruction']) . "\n\n";
        }

        if ($result->aiReport) {
            if (empty($result->metadata['custom_instruction'])) {
                $md .= "---\n\n";
            }
            $md .= "## AI Insights\n\n";
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
                $typeLabel  = !empty($data['title']) ? $data['title'] : ucwords(str_replace('_', ' ', $key));
                $confidence = $data['confidence'] ?? '';
                $sevEmoji   = match (strtolower($data['severity'] ?? 'info')) {
                    'critical' => '🔴',
                    'high'     => '🟠',
                    'warning'  => '🟡',
                    default    => '🔵',
                };
                $confBadge  = match ($confidence) {
                    'high'   => ' ✅ `high confidence`',
                    'medium' => ' ⚠️ `medium confidence`',
                    'low'    => ' 💡 `low confidence`',
                    default  => '',
                };

                $md .= "### {$sevEmoji} {$typeLabel}{$confBadge}\n\n";
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
        $typeLabel = !empty($issue['title']) ? $issue['title'] : ucwords(str_replace('_', ' ', $issue['type'] ?? 'unknown'));
        $sev       = strtolower($issue['severity'] ?? 'info');
        $sevEmoji  = match ($sev) {
            'critical' => '🔴',
            'high'     => '🟠',
            'warning'  => '🟡',
            default    => '🔵',
        };
        $confidence  = $issue['confidence'] ?? '';
        $ruleType    = $issue['rule_type']  ?? '';
        $confBadge   = match ($confidence) {
            'high'   => ' `✔ high confidence`',
            'medium' => ' `⚠️ medium confidence`',
            'low'    => ' `💡 low confidence`',
            default  => '',
        };

        $md  = "#### {$sevEmoji} {$typeLabel}{$confBadge}";
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
        if (!empty($ruleType)) {
            $md .= "**Rule Type:** `{$ruleType}`  \n";
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
        if (!empty($issue['note'])) {
            $md .= "\n> 📌 *Note: {$issue['note']}*\n";
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
