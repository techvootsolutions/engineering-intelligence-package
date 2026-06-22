<?php
namespace Dev\EipAgent\Analyzers;

use Dev\EipAgent\Rules\EnvHelperUsageRule;
use Dev\EipAgent\Rules\RawSqlRule;

class SecurityAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new EnvHelperUsageRule(),
            new RawSqlRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        // Security rules scan the entire codebase, filtering happens inside each rule
        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($files)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
