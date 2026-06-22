<?php

namespace Dev\EipAgent\AI\Chunking;

class IssueCategoryClassifier
{
    private array $mapping = [
        'fat_controller'       => 'architecture',
        'n_plus_one'           => 'performance',
        'missing_form_request' => 'validation',
        'long_method'          => 'complexity',
        'god_class'            => 'architecture',
        'too_many_parameters'  => 'complexity',
        'dead_code'            => 'maintainability',
        'empty_catch_block'    => 'security',
        'hardcoded_secrets'    => 'security',
        'sql_injection_risk'   => 'security',
        'duplication'          => 'duplication',
        'dependency_cycle'     => 'dependencies',
        // Fallback for anything else
    ];

    public function classify(string $issueType): string
    {
        return $this->mapping[$issueType] ?? 'maintainability';
    }

    public function addMapping(string $issueType, string $category): void
    {
        $this->mapping[$issueType] = $category;
    }
}
