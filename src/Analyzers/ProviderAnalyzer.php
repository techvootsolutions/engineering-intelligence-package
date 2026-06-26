<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\HeavyServiceProviderBootRule;

class ProviderAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new HeavyServiceProviderBootRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $providers = array_filter(
            $files,
            fn($file) => $file->classification === 'providers'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($providers)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
