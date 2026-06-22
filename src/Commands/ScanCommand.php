<?php
namespace Dev\EipAgent\Commands;

use Dev\EipAgent\Reporting\ConsoleUIService;
use Dev\EipAgent\Reporting\ReportManager;
use Dev\EipAgent\Services\ReportGenerator;
use Illuminate\Console\Command;

class ScanCommand extends Command
{
    protected $signature = 'eip
        {--json        : Output report in JSON format}
        {--markdown    : Output report in Markdown format}
        {--export      : Generate all reports (JSON + Markdown)}
        {--output=     : Custom output directory or file path}
        {--severity=   : Filter console output by severity (critical, high, warning, info)}
        {--type=       : Filter console output by issue type slug (e.g. n_plus_one)}
        {--file=       : Partial filename filter for console output (e.g. UserController)}
        {--limit=      : Cap console output to N issues}
        {--sort=       : Sort field: severity (default), file, line}';

    protected $description = 'Engineering Intelligence Package — run a full project scan';

    public function handle(
        ReportGenerator  $generator,
        ReportManager    $reportManager,
        ConsoleUIService $ui
    ): int {
        // Give the UI service a reference to this command for output methods
        $ui->setCommand($this);

        // Track overall execution time from this point
        $startTime = microtime(true);

        // Print the enterprise header
        $ui->header('Engineering Intelligence Package');

        $result = null;

        try {
            // Collect sub-step messages during the scan so they can be printed
            // after the spinner finishes (spinners and direct output cannot run together).
            $subSteps = [];

            // ── Step 1 & 2: File Discovery + Analyzers ─────────────────────────
            $this->line('🔍 <fg=white>Discovering and analysing project files...</>');

            $result = $generator->generate(function (string $event, mixed $data) use (&$subSteps, $ui) {
                match ($event) {
                    'files_discovered'    => $subSteps[] = "📁 {$data} files discovered",
                    'analyzer_completed'  => $subSteps[] = "  ✓ {$data}",
                    'issues_aggregated'   => $subSteps[] = "📊 {$data} issues aggregated",
                    'hotspots_calculated' => $subSteps[] = "🔥 {$data} hotspot files identified",
                    'context_built'       => $subSteps[] = "🤖 AI context compressed successfully",
                    'ai_completed'        => $subSteps[] = "✅ AI analysis completed",
                    'ai_failed'           => $subSteps[] = "⚠️  AI analysis failed: {$data}",
                    default               => null,
                };
            });

            // Print all collected sub-steps now that the scan is done
            foreach ($subSteps as $line) {
                $this->line("  <fg=gray>{$line}</>");
            }

            $this->newLine();

        } catch (\Exception $e) {
            $this->error("❌ Scan failed: " . $e->getMessage());
            return self::FAILURE;
        }

        // ── Step 3: Print active filters if any ────────────────────────────
        $this->printActiveFilters();

        // ── Step 4: Persist report files ───────────────────────────────────
        $this->line('📄 <fg=white>Generating reports...</>');

        $generatedFiles = $reportManager->handle($result, [
            'json'     => $this->option('json'),
            'markdown' => $this->option('markdown'),
            'export'   => $this->option('export'),
            'output'   => $this->option('output'),
        ]);

        foreach ($generatedFiles as $file) {
            $this->line("  <fg=green>✓</> " . basename($file));
        }

        $this->newLine();

        // ── Step 5: Print the full summary table ───────────────────────────
        $printer = app(\Dev\EipAgent\Reporting\ConsoleSummaryPrinter::class);
        $printer->print($this, $result, []);

        // ── Footer ─────────────────────────────────────────────────────────
        $elapsed = (microtime(true) - $startTime) * 1000;
        $ui->footer($result, $elapsed);

        return self::SUCCESS;
    }

    private function printActiveFilters(): void
    {
        $active = array_filter([
            'severity' => $this->option('severity'),
            'type'     => $this->option('type'),
            'file'     => $this->option('file'),
            'limit'    => $this->option('limit'),
        ]);

        if (!empty($active)) {
            $this->newLine();
            $this->line('<fg=yellow>Active filters:</>');
            foreach ($active as $key => $val) {
                $this->line("  --{$key}={$val}");
            }
            $this->newLine();
        }
    }
}
