<?php

namespace Dev\EipAgent\Services;

use Dev\EipAgent\Analyzers\BaseAnalyzer;
use Dev\EipAgent\DTOs\FileResult;

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
     * @return array{issues: \Dev\EipAgent\DTOs\Issue[], rules_executed: int}
     */
    public function execute(array $files, ?\Closure $onProgress = null): array
    {
        $allIssues = [];
        $rulesExecuted = 0;

        foreach ($this->analyzers as $analyzer) {
            $issues = $analyzer->analyze($files);
            $allIssues = array_merge($allIssues, $issues);
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
