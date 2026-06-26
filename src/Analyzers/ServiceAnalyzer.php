<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\ServiceGodClassRule;
use Techvoot\EIP\Rules\ServiceDependencyOverloadRule;
use Techvoot\EIP\Rules\PotentialCircularDependencyRule;

class ServiceAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new ServiceGodClassRule(),
            new ServiceDependencyOverloadRule(),
            new PotentialCircularDependencyRule(),
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
