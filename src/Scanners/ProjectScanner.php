<?php
namespace Techvoot\EIP\Scanners;

use Techvoot\EIP\DTOs\ScanResult;
use Techvoot\EIP\Services\FileDiscoveryService;
use Techvoot\EIP\Services\HealthScoreCalculator;
use Techvoot\EIP\Services\IssueBreakdownGenerator;
use Techvoot\EIP\Services\LaravelVersionDetector;
use Techvoot\EIP\Services\RiskSummaryGenerator;
use Techvoot\EIP\Services\RuleEngine;

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

    public function scan(?\Closure $onProgress = null, array $filters = []): ScanResult
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

        // Apply filters globally to the issue array
        $issuesArray = $this->applyFilters($issuesArray, $filters);

        // 4. Calculate Health & Summaries based ONLY on filtered issues
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

    private function applyFilters(array $issues, array $filters): array
    {
        if (empty($filters)) {
            return $issues;
        }

        if (!empty($filters['severity'])) {
            $sev = strtolower($filters['severity']);
            $issues = array_filter($issues, fn ($i) => strtolower($i['severity'] ?? '') === $sev);
        }
        if (!empty($filters['type'])) {
            $type = strtolower($filters['type']);
            $issues = array_filter($issues, fn ($i) => strtolower($i['type'] ?? '') === $type);
        }
        if (!empty($filters['file'])) {
            $file = strtolower($filters['file']);
            $issues = array_filter($issues, fn ($i) => str_contains(strtolower($i['file'] ?? ''), $file));
        }

        // Apply sorting
        if (!empty($filters['sort'])) {
            $sort = strtolower($filters['sort']);
            usort($issues, function ($a, $b) use ($sort) {
                if ($sort === 'file') return strcmp($a['file'] ?? '', $b['file'] ?? '');
                if ($sort === 'line') return ($a['line'] ?? 0) <=> ($b['line'] ?? 0);
                
                $wA = $this->severityWeight($a['severity'] ?? '');
                $wB = $this->severityWeight($b['severity'] ?? '');
                if ($wA === $wB) {
                    return strcmp($a['file'] ?? '', $b['file'] ?? '');
                }
                return $wB <=> $wA;
            });
        }

        // Re-index array after filtering
        $issues = array_values($issues);

        if (!empty($filters['limit'])) {
            $issues = array_slice($issues, 0, (int)$filters['limit']);
        }

        return $issues;
    }

    private function severityWeight(string $severity): int
    {
        return match (strtolower($severity)) {
            'critical' => 4,
            'high'     => 3,
            'warning'  => 2,
            'info'     => 1,
            default    => 0,
        };
    }
}
