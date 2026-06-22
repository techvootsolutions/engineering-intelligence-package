<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\Rules\BaseRule;

class MissingTransactionRule extends BaseRule
{
    public function analyze(array $controllers): array
    {
        $issues = [];

        foreach ($controllers as $controller) {

            $content = file_get_contents(
                $controller->path
            );

            $createCount = substr_count(
                $content,
                '::create('
            );

            $hasTransaction =
                str_contains(
                $content,
                'DB::transaction('
            );

            if ($createCount >= 2 && !$hasTransaction) {
                $issues[] = $this->issue(
                    type: 'missing_transaction',
                    severity: 'critical',
                    file: $controller->relativePath,
                    message: sprintf(
                        '%s contains %d create operations without transaction.',
                        $controller->relativePath,
                        $createCount
                    ),
                    impact: 'Multiple database writes without transaction.',
                    recommendation: 'Wrap operations in DB::transaction().',
                    score: 15,
                    extra: [
                        'create_operations' => $createCount,
                    ]
                );
            }
        }

        return $issues;
    }
}
