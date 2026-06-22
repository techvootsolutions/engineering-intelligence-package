<?php
namespace Dev\EipAgent\Reporting;

use Dev\EipAgent\DTOs\ScanResult;
use Illuminate\Console\Command;
use function Laravel\Prompts\spin;

class ConsoleUIService
{
    private Command $command;

    public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    public function header(string $title): void
    {
        $this->command->newLine();
        $this->command->line('<fg=cyan;options=bold>══════════════════════════════════</>');
        $this->command->line("<fg=white;options=bold> {$title}</>");
        $this->command->line('<fg=cyan;options=bold>══════════════════════════════════</>');
        $this->command->newLine();
    }

    /**
     * Wrap a callback in a loading spinner
     */
    public function step(string $message, \Closure $callback): mixed
    {
        return spin($callback, $message);
    }

    /**
     * Print a sub-step success row
     */
    public function successRow(string $message): void
    {
        $this->command->line("  <fg=green>✓</> <fg=gray>{$message}</>");
    }

    /**
     * Print a major step success
     */
    public function successMain(string $message): void
    {
        $this->command->line("<fg=green>✓</> <fg=white>{$message}</>");
        $this->command->newLine();
    }

    public function footer(ScanResult $result, float $durationMs): void
    {
        $healthScore = $result->health['health_score'] ?? 0;
        $issueCount  = count($result->issues);
        $criticals   = count(array_filter($result->issues, fn($i) => ($i['severity'] ?? '') === 'critical'));
        
        $durationSec = number_format($durationMs / 1000, 1);

        $this->command->newLine();
        $this->command->line('<fg=cyan;options=bold>══════════════════════════════════</>');
        $this->command->line('<fg=green;options=bold> Scan Completed Successfully</>');
        $this->command->line('<fg=cyan;options=bold>══════════════════════════════════</>');
        $this->command->newLine();

        $this->command->line("<fg=white;options=bold>Health Score:</> {$healthScore}/100");
        $this->command->line("<fg=white;options=bold>Critical Issues:</> {$criticals}");
        $this->command->line("<fg=white;options=bold>Total Issues:</> {$issueCount}");
        $this->command->line("<fg=white;options=bold>Execution Time:</> {$durationSec}s");
        $this->command->newLine();
    }
}
