<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\MissingStrictTypesRule;

class QualityAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new MissingStrictTypesRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        // Quality rules scan the entire codebase
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
