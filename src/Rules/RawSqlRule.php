<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\DTOs\FileResult;

class RawSqlRule extends BaseRule
{
    /**
     * Detect raw SQL queries where a PHP variable is concatenated or interpolated directly into the string.
     * This is a classic SQL injection vector.
     *
     * @param FileResult[] $files
     */
    public function analyze(array $files): array
    {
        $issues = [];

        foreach ($files as $file) {
            $content = $file->content;

            $hasRawWithVar = preg_match('/DB::raw\(.*\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $content)
                          || preg_match('/whereRaw\(.*\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $content)
                          || preg_match('/selectRaw\(.*\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $content)
                          || preg_match('/havingRaw\(.*\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $content);

            if ($hasRawWithVar) {
                $issues[] = $this->issue(
                    type: 'potential_sql_injection',
                    severity: 'critical',
                    file: $file->relativePath,
                    message: "A raw SQL query contains a PHP variable that may be user-controlled.",
                    impact: "SQL Injection is one of the most critical security vulnerabilities. An attacker could read, modify, or delete your entire database.",
                    recommendation: "Always use parameter bindings (?) for dynamic values in raw queries. Example: DB::raw('column = ?', [\$value])",
                    score: 30
                );
            }
        }

        return $issues;
    }
}
