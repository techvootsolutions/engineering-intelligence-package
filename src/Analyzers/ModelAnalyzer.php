<?php
namespace Dev\EipAgent\Analyzers;

use Dev\EipAgent\Rules\MassAssignmentRule;

class ModelAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new MassAssignmentRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $models = array_filter(
            $files,
            fn($file) => $file->classification === 'models'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($models)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
