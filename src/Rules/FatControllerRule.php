<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\Rules\BaseRule;

class FatControllerRule extends BaseRule
{
    private int $maxLines;

    public function __construct(int $maxLines = 500)
    {
        $this->maxLines = $maxLines;
    }

    public function analyze(array $controllers): array
    {
        $issues = [];

        foreach ($controllers as $controller) {
            $lines = substr_count($controller->content, "\n") + 1;

            if ($lines > $this->maxLines) {
                $issues[] = $this->issue(
                    type: 'fat_controller',
                    severity: 'warning',
                    file: $controller->relativePath,
                    message: sprintf(
                        '%s has %d lines.',
                        $controller->relativePath,
                        $lines
                    ),
                    impact: 'Large controllers usually contain too much business logic.',
                    recommendation: 'Move logic into services or actions.',
                    score: 5,
                    extra: [
                        'lines' => $lines,
                    ]
                );
            }
        }

        return $issues;
    }
}
