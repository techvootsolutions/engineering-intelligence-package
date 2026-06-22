<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\SyncDispatchRule;

class JobAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new SyncDispatchRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $jobs = array_filter(
            $files,
            fn($file) => $file->classification === 'jobs'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($jobs)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
