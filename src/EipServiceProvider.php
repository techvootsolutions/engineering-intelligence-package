<?php
namespace Techvoot\EIP;

use Techvoot\EIP\Commands\ScanCommand;
use Techvoot\EIP\IssueOrganizer\HotspotCalculator;
use Techvoot\EIP\IssueOrganizer\IssueOrganizer;
use Techvoot\EIP\Reporting\ConsoleSummaryPrinter;
use Techvoot\EIP\Reporting\ConsoleUIService;
use Techvoot\EIP\Reporting\JsonReportGenerator;
use Techvoot\EIP\Reporting\MarkdownReportGenerator;
use Techvoot\EIP\Reporting\ReportManager;
use Illuminate\Support\ServiceProvider;

class EipServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/eip.php', 'eip'
        );

        // ── Analyzer Registry (Rule Engine) ────────────────────────────────
        $this->app->singleton(\Techvoot\EIP\Services\RuleEngine::class, function ($app) {
            $engine = new \Techvoot\EIP\Services\RuleEngine();

            // Register all file-type specific analyzers
            $engine->addAnalyzer(new \Techvoot\EIP\Analyzers\ControllerAnalyzer());
            $engine->addAnalyzer(new \Techvoot\EIP\Analyzers\ModelAnalyzer());
            $engine->addAnalyzer(new \Techvoot\EIP\Analyzers\ServiceAnalyzer());
            $engine->addAnalyzer(new \Techvoot\EIP\Analyzers\RouteAnalyzer());
            $engine->addAnalyzer(new \Techvoot\EIP\Analyzers\JobAnalyzer());
            $engine->addAnalyzer(new \Techvoot\EIP\Analyzers\EventAnalyzer());

            // Security analyzer scans the entire codebase for vulnerabilities
            $engine->addAnalyzer(new \Techvoot\EIP\Analyzers\SecurityAnalyzer());

            return $engine;
        });

        // ── Reporting Services ─────────────────────────────────────────────
        $this->app->singleton(JsonReportGenerator::class);
        $this->app->singleton(MarkdownReportGenerator::class);
        $this->app->singleton(ConsoleSummaryPrinter::class);

        // The ConsoleUIService is a lightweight, stateful helper for CLI output.
        // It must be bound so the ScanCommand can receive it via DI.
        $this->app->singleton(ConsoleUIService::class);

        $this->app->singleton(ReportManager::class, function ($app) {
            return new ReportManager(
                $app->make(JsonReportGenerator::class),
                $app->make(MarkdownReportGenerator::class),
                $app->make(\Techvoot\EIP\AI\Reports\AIFinalReportGenerator::class)
            );
        });

        // ── Issue Organizer Engine ─────────────────────────────────────────
        $this->app->singleton(IssueOrganizer::class);
        $this->app->singleton(HotspotCalculator::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScanCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/eip.php' =>
            config_path('eip.php'),
        ], 'eip-config');
    }
}
