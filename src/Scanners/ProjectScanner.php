<?php
namespace Dev\EipAgent\Scanners;

use Dev\EipAgent\DTOs\ScanResult;
use Dev\EipAgent\Services\FileDiscoveryService;
use Dev\EipAgent\Services\HealthScoreCalculator;
use Dev\EipAgent\Services\IssueBreakdownGenerator;
use Dev\EipAgent\Services\LaravelVersionDetector;
use Dev\EipAgent\Services\RiskSummaryGenerator;
use Dev\EipAgent\Services\RuleEngine;

class ProjectScanner
{
    public function __construct(
        private FileDiscoveryService $fileDiscoveryService,
        private LaravelVersionDetector $laravelVersionDetector,
        private RuleEngine $ruleEngine,
        private HealthScoreCalculator $healthScoreCalculator,
        private IssueBreakdownGenerator $issueBreakdownGenerator,
        private RiskSummaryGenerator $riskSummaryGenerator
    ) {
    }

    public function scan(?\Closure $onProgress = null): ScanResult
    {
        $basePath = base_path();
        
        $composerFile = $basePath . '/composer.json';
        if (!file_exists($composerFile)) {
            throw new \Exception('composer.json not found');
        }

        $composer = json_decode(file_get_contents($composerFile), true);

        // 1. Detect Laravel Version
        $laravelVersion = $this->laravelVersionDetector->detect($basePath);

        // 2. Discover & Classify Files
        $discoveryResult = $this->fileDiscoveryService->discover($basePath);
        $files = $discoveryResult['files'];
        $metrics = $discoveryResult['metrics'];

        if ($onProgress) {
            $onProgress('files_discovered', count($files));
        }

        // 3. Execute Rule Engine
        $analysis = $this->ruleEngine->execute($files, $onProgress);

        $issuesArray = array_map(fn($issue) => $issue->toArray(), $analysis['issues']);

        // 4. Calculate Health & Summaries
        $health = $this->healthScoreCalculator->calculate($issuesArray, count($files));
        $issueBreakdown = $this->issueBreakdownGenerator->generate($issuesArray);
        $summary = $this->riskSummaryGenerator->generate($issuesArray);

        return new ScanResult(
            projectName: $composer['name'] ?? 'Unknown',
            projectType: $composer['type'] ?? 'project',
            rulesExecuted: $analysis['rules_executed'],
            health: $health,
            metrics: $metrics->toArray(),
            details: [
                'laravel_version' => $laravelVersion,
                'total_files_scanned' => count($files),
            ],
            issues: $issuesArray,
            issueBreakdown: $issueBreakdown,
            summary: $summary
        );
    }
}
