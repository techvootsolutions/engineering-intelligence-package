<?php
namespace Techvoot\EIP\Rules;

class MissingDatabaseIndexRule extends BaseRule
{
    public function analyze(array $migrations): array
    {
        $issues = [];
        foreach ($migrations as $migration) {
            // Check for foreign key columns that don't have an index
            // Typically ->foreignId('user_id') automatically adds an index, but ->unsignedBigInteger('user_id') does not unless ->index() is called.
            if (preg_match_all('/(?:unsignedBigInteger|unsignedInteger|integer)\(\s*[\'"](.*?(?:_id))[\'"]\s*\)(?!.*->index\(\))/Uis', $migration->content, $matches)) {
                foreach ($matches[1] as $column) {
                    $issues[] = $this->issue(
                        type: 'missing_database_index',
                        file: $migration->relativePath,
                        message: sprintf('Foreign key column "%s" might be missing an index.', $column),
                        impact: 'Querying by this foreign key could be slow (table scan).',
                        recommendation: 'Add ->index() or ->foreign() constraint to the column.'
                    );
                }
            }
        }
        return $issues;
    }
}
