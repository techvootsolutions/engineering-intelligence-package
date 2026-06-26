<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\DuplicateHelperFunctionRule;

class HelperAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new DuplicateHelperFunctionRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $helpers = array_filter(
            $files,
            fn($file) => $file->classification === 'helpers'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($helpers)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
