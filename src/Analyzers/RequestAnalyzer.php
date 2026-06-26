<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\RequestMissingAuthorizationRule;
use Techvoot\EIP\Rules\EmptyValidationRulesRule;

class RequestAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new RequestMissingAuthorizationRule(),
            new EmptyValidationRulesRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $requests = array_filter(
            $files,
            fn($file) => $file->classification === 'requests'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($requests)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
