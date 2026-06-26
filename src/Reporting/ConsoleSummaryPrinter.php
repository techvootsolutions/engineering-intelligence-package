<?php
namespace Techvoot\EIP\Reporting;

use Techvoot\EIP\DTOs\ScanResult;
use Illuminate\Console\Command;

class ConsoleSummaryPrinter
{
    public function print(Command $command, ScanResult $result, array $generatedFiles = [], array $activeFilters = []): void
    {
        $command->newLine();
        $command->line('<fg=cyan;options=bold>╔══════════════════════════════════════╗</>');
        $command->line('<fg=cyan;options=bold>║   Engineering Intelligence Package   ║</>');
        $command->line('<fg=cyan;options=bold>╚══════════════════════════════════════╝</>');
        $command->newLine();

        // ── Health summary ─────────────────────────────────────────────────
        $healthScore   = $result->health['health_score'] ?? 0;
        $grade         = $this->getGrade($healthScore);
        $gradeColor    = $this->gradeColor($grade);
        $issueCount    = count($result->issues);

        $criticalCount = count(array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'critical'));
        $highCount     = count(array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'high'));
        $warningCount  = count(array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'warning'));
        $infoCount     = count(array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'info'));

        $command->line("  <fg=white;options=bold>Overall Health :</> <fg={$gradeColor};options=bold>{$healthScore} ({$grade})</>");
        $command->newLine();
        
        $sec = $result->health['security_score'] ?? 100;
        $arch = $result->health['architecture_score'] ?? 100;
        $perf = $result->health['performance_score'] ?? 100;
        $qual = $result->health['quality_score'] ?? 100;
        
        $command->line("  <fg=white;options=bold>Security       :</> {$sec}");
        $command->line("  <fg=white;options=bold>Architecture   :</> {$arch}");
        $command->line("  <fg=white;options=bold>Performance    :</> {$perf}");
        $command->line("  <fg=white;options=bold>Code Quality   :</> {$qual}");
        $command->newLine();

        $command->line("  <fg=white;options=bold>Total Issues   :</> {$issueCount}");
        $command->line("  <fg=red>🔴 Critical     :</> {$criticalCount}");
        $command->line("  <fg=yellow>🟠 High         :</> {$highCount}");
        $command->line("  <fg=yellow>🟡 Warnings     :</> {$warningCount}");
        $command->line("  <fg=blue>🔵 Info         :</> {$infoCount}");
        $command->newLine();

        // ── Finding Breakdown ─────────────────────────────────────────────
        if (!empty($result->findingBreakdown)) {
            $det  = $result->findingBreakdown['deterministic']  ?? 0;
            $heur = $result->findingBreakdown['heuristic']      ?? 0;
            $arch = $result->findingBreakdown['architectural']  ?? 0;

            $command->line('<fg=white;options=bold>  📊 Finding Breakdown</>');
            $command->newLine();
            $command->line("  <fg=green>✔ Deterministic (Verified)    :</> {$det}");
            $command->line("  <fg=yellow>~ Heuristic (Pattern-Based)  :</> {$heur}");
            $command->line("  <fg=cyan>⬡ Architectural (Opinion)     :</> {$arch}");
            $command->newLine();
        }

        // ── Risk Hotspot files (top 5) ─────────────────────────────────────
        if (!empty($result->hotspots)) {
            $command->line('<fg=white;options=bold>  📍 Risk Hotspot Files</>');
            $command->newLine();

            $rows = [];
            foreach (array_slice($result->hotspots, 0, 5) as $h) {
                $bar  = $this->riskBar($h['risk_score']);
                $cats = $h['categories'] ?? [];
                $rows[] = [
                    basename($h['file']),
                    $h['risk_score'],
                    $h['issue_count'],
                    $h['critical_count'],
                    ($cats['security']     ?? 0) . 'S / ' .
                    ($cats['architecture'] ?? 0) . 'A / ' .
                    ($cats['performance']  ?? 0) . 'P / ' .
                    ($cats['quality']      ?? 0) . 'Q',
                    $bar,
                ];
            }

            $command->table(
                ['File', 'Risk Score', 'Issues', 'Critical', 'Categories', 'Severity Bar'],
                $rows
            );
        }

        // ── Filtered Issues / Top Critical Issues ────────────────────────────────
        $issues = $result->issues;
        $isFiltered = !empty($activeFilters);

        if (!$isFiltered) {
            // Default behavior: only show up to 10 criticals
            $issues = array_filter($issues, fn ($i) => ($i['severity'] ?? '') === 'critical');
            $issues = array_slice($issues, 0, 10);
        }

        if (!empty($issues)) {
            $title = $isFiltered ? '  🔍 Filtered Issues' : '  🔴 Top Critical Issues';
            $command->line("<fg=white;options=bold>{$title}</>");
            $command->newLine();

            $rows = [];
            foreach ($issues as $issue) {
                $severity       = ucfirst($issue['severity'] ?? 'Unknown');
                $confidence     = $issue['confidence'] ?? 'medium';
                $classification = $issue['classification'] ?? 'manual_review_required';

                $confidenceLabel = match ($confidence) {
                    'high'   => '<fg=green>High</>',
                    'medium' => '<fg=yellow>Medium</>',
                    'low'    => '<fg=cyan>Low</>',
                    default  => $confidence,
                };

                $classLabel = $classification === 'verified'
                    ? '<fg=green>✔ Verified</>'
                    : '<fg=yellow>⚠ Manual Review</>';

                $rows[] = [
                    $issue['id']   ?? '—',
                    $severity,
                    $confidenceLabel,
                    $classLabel,
                    basename($issue['file'] ?? '—'),
                    $issue['line'] > 0 ? $issue['line'] : '—',
                    $this->truncate($issue['title'] ?? ucwords(str_replace('_', ' ', $issue['type'] ?? '—')), 45),
                ];
            }

            $command->table(
                ['ID', 'Severity', 'Confidence', 'Classification', 'File', 'Line', 'Finding'],
                $rows
            );
        } elseif ($isFiltered) {
            $command->line('<fg=yellow;options=bold>  🔍 No issues found matching the given filters.</>');
            $command->newLine();
        }

        // ── Generated files ────────────────────────────────────────────────
        if (!empty($generatedFiles)) {
            $command->line('<fg=green>  ✅ Reports Generated:</>');
            foreach ($generatedFiles as $file) {
                $command->line("     <fg=green>→</> {$file}");
            }
            $command->newLine();
        }
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

    private function gradeColor(string $grade): string
    {
        return match ($grade) {
            'A'     => 'green',
            'B'     => 'cyan',
            'C'     => 'yellow',
            'D'     => 'yellow',
            default => 'red',
        };
    }

    private function riskBar(int $score): string
    {
        $filled = (int) round($score / 10);
        $empty  = 10 - $filled;
        $color  = $score >= 70 ? 'red' : ($score >= 40 ? 'yellow' : 'green');
        return "<fg={$color}>" . str_repeat('█', $filled) . str_repeat('░', $empty) . "</> {$score}";
    }

    private function truncate(string $str, int $max): string
    {
        return mb_strlen($str) > $max ? mb_substr($str, 0, $max - 1) . '…' : $str;
    }
}
