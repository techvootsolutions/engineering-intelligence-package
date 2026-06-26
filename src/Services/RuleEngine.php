<?php

namespace Techvoot\EIP\Services;

use Techvoot\EIP\Analyzers\BaseAnalyzer;
use Techvoot\EIP\DTOs\FileResult;

class RuleEngine
{
    /** @var BaseAnalyzer[] */
    private array $analyzers = [];

    public function addAnalyzer(BaseAnalyzer $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }

    /**
     * @param FileResult[] $files
     * @param \Closure|null $onProgress
     * @return array{issues: \Techvoot\EIP\DTOs\Issue[], rules_executed: int}
     */
    public function execute(array $files, ?\Closure $onProgress = null): array
    {
        $allIssues = [];
        $rulesExecuted = 0;

        foreach ($this->analyzers as $analyzer) {
            $issues = $analyzer->analyze($files);
            
            $disabledRules = config('eip.disabled_rules', []);
            $filteredIssues = array_filter($issues, fn($issue) => !in_array($issue->type, $disabledRules));
            
            $allIssues = array_merge($allIssues, $filteredIssues);
            $rulesExecuted += $analyzer->getRulesCount();

            if ($onProgress) {
                $analyzerName = class_basename($analyzer);
                $onProgress('analyzer_completed', $analyzerName);
            }
        }

        return [
            'issues' => $allIssues,
            'rules_executed' => $rulesExecuted,
        ];
    }
}
