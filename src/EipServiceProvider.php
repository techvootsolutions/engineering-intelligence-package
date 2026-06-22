<?php
namespace Dev\EipAgent;

use Dev\EipAgent\Commands\ScanCommand;
use Dev\EipAgent\IssueOrganizer\HotspotCalculator;
use Dev\EipAgent\IssueOrganizer\IssueOrganizer;
use Dev\EipAgent\Reporting\ConsoleSummaryPrinter;
use Dev\EipAgent\Reporting\ConsoleUIService;
use Dev\EipAgent\Reporting\JsonReportGenerator;
use Dev\EipAgent\Reporting\MarkdownReportGenerator;
use Dev\EipAgent\Reporting\ReportManager;
use Illuminate\Support\ServiceProvider;

class EipServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/eip.php', 'eip'
        );

        // ── Analyzer Registry (Rule Engine) ────────────────────────────────
        $this->app->singleton(\Dev\EipAgent\Services\RuleEngine::class, function ($app) {
            $engine = new \Dev\EipAgent\Services\RuleEngine();

            // Register all file-type specific analyzers
            $engine->addAnalyzer(new \Dev\EipAgent\Analyzers\ControllerAnalyzer());
            $engine->addAnalyzer(new \Dev\EipAgent\Analyzers\ModelAnalyzer());
            $engine->addAnalyzer(new \Dev\EipAgent\Analyzers\ServiceAnalyzer());
            $engine->addAnalyzer(new \Dev\EipAgent\Analyzers\RouteAnalyzer());
            $engine->addAnalyzer(new \Dev\EipAgent\Analyzers\JobAnalyzer());
            $engine->addAnalyzer(new \Dev\EipAgent\Analyzers\EventAnalyzer());

            // Security analyzer scans the entire codebase for vulnerabilities
            $engine->addAnalyzer(new \Dev\EipAgent\Analyzers\SecurityAnalyzer());

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
                $app->make(\Dev\EipAgent\AI\Reports\AIFinalReportGenerator::class)
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
