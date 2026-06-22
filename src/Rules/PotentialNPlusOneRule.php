<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\Rules\BaseRule;

class PotentialNPlusOneRule extends BaseRule
{
    public function analyze(array $controllers): array
    {
        $issues = [];

        foreach ($controllers as $controller) {

            $content = $controller->content;

            $hasForeach = str_contains(
                $content,
                'foreach'
            );

            $hasRelationAccess = preg_match(
                '/->([a-zA-Z_][a-zA-Z0-9_]*)/',
                $content
            );

            $hasEagerLoading = str_contains(
                $content,
                'with('
            );

            if ($hasForeach && $hasRelationAccess && !$hasEagerLoading) {
                $issues[] = $this->issue(
                    type: 'potential_n_plus_one',
                    severity: 'warning',
                    file: $controller->relativePath,
                    message: "{$controller->relativePath} may contain an N+1 query issue.",
                    impact: 'May trigger excessive database queries.',
                    recommendation: 'Use eager loading with with().',
                    score: 5
                );
            }
        }

        return $issues;
    }
}
