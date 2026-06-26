<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\MigrationMissingRollbackRule;
use Techvoot\EIP\Rules\MissingDatabaseIndexRule;

class MigrationAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new MigrationMissingRollbackRule(),
            new MissingDatabaseIndexRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $migrations = array_filter(
            $files,
            fn($file) => $file->classification === 'migrations'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($migrations)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
