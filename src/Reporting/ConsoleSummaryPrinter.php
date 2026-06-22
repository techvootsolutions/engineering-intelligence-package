<?php
namespace Dev\EipAgent\Reporting;

use Dev\EipAgent\DTOs\ScanResult;
use Illuminate\Console\Command;

class ConsoleSummaryPrinter
{
    public function print(Command $command, ScanResult $result, array $generatedFiles = []): void
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

        $command->line("  <fg=white;options=bold>Health Score :</> <fg={$gradeColor};options=bold>{$healthScore} ({$grade})</>");
        $command->line("  <fg=white;options=bold>Total Issues :</> {$issueCount}");
        $command->line("  <fg=red>🔴 Critical   :</> {$criticalCount}");
        $command->line("  <fg=yellow>🟠 High       :</> {$highCount}");
        $command->line("  <fg=yellow>🟡 Warnings   :</> {$warningCount}");
        $command->line("  <fg=blue>🔵 Info       :</> {$infoCount}");
        $command->newLine();

        // ── Risk Hotspot files (top 5) ─────────────────────────────────────
        if (!empty($result->hotspots)) {
            $command->line('<fg=white;options=bold>  📍 Risk Hotspot Files</>');
            $command->newLine();

            $rows = [];
            foreach (array_slice($result->hotspots, 0, 5) as $h) {
                $bar        = $this->riskBar($h['risk_score']);
                $rows[]     = [
                    basename($h['file']),
                    $h['risk_score'],
                    $h['issue_count'],
                    $h['critical_count'],
                    $bar,
                ];
            }

            $command->table(
                ['File', 'Risk Score', 'Issues', 'Critical', 'Severity Bar'],
                $rows
            );
        }

        // ── Top critical issues (up to 10) ────────────────────────────────
        $criticals = array_filter($result->issues, fn ($i) => ($i['severity'] ?? '') === 'critical');
        if (!empty($criticals)) {
            $command->line('<fg=white;options=bold>  🔴 Top Critical Issues</>');
            $command->newLine();

            $rows = [];
            foreach (array_slice(array_values($criticals), 0, 10) as $issue) {
                $rows[] = [
                    $issue['id']   ?? '—',
                    basename($issue['file'] ?? '—'),
                    $issue['line'] > 0 ? $issue['line'] : '—',
                    ucwords(str_replace('_', ' ', $issue['type'] ?? '—')),
                    $this->truncate($issue['message'] ?? '', 55),
                ];
            }

            $command->table(
                ['ID', 'File', 'Line', 'Type', 'Message'],
                $rows
            );
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
