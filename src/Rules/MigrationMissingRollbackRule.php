<?php
namespace Techvoot\EIP\Rules;

class MigrationMissingRollbackRule extends BaseRule
{
    public function analyze(array $migrations): array
    {
        $issues = [];
        foreach ($migrations as $migration) {
            if (preg_match('/public function down\(\)\s*(?::\s*void\s*)?\{\s*\}/is', $migration->content)) {
                $issues[] = $this->issue(
                    type: 'migration_missing_rollback',
                    file: $migration->relativePath,
                    message: 'Migration has an empty down() method.',
                    impact: 'Database cannot be cleanly rolled back.',
                    recommendation: 'Implement the rollback logic in the down() method.'
                );
            }
        }
        return $issues;
    }
}
