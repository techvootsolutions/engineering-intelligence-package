<?php
namespace Dev\EipAgent\Analyzers;

use Dev\EipAgent\Rules\ServiceGodClassRule;

class ServiceAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new ServiceGodClassRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $services = array_filter(
            $files,
            fn($file) => $file->classification === 'services'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($services)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
