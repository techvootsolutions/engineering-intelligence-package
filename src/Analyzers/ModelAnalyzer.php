<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\MassAssignmentRule;
use Techvoot\EIP\Rules\UnguardedModelRule;
use Techvoot\EIP\Rules\MissingRelationshipReturnTypeRule;

class ModelAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new MassAssignmentRule(),
            new UnguardedModelRule(),
            new MissingRelationshipReturnTypeRule(),
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
