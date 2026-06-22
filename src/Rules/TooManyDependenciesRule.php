<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\Rules\BaseRule;

class TooManyDependenciesRule extends BaseRule
{
    private int $maxDependencies;

    public function __construct(int $maxDependencies = 5)
    {
        $this->maxDependencies = $maxDependencies;
    }

    public function analyze(array $controllers): array
    {
        $issues = [];

        foreach ($controllers as $controller) {
            preg_match(
                '/__construct\s*\((.*?)\)/s',
                $controller->content,
                $matches
            );

            if (!isset($matches[1])) {
                continue;
            }

            $dependencies = array_filter(
                array_map('trim', explode(',', $matches[1]))
            );

            $count = count($dependencies);

            if ($count > $this->maxDependencies) {

                $issues[] = $this->issue(
                    type: 'too_many_dependencies',
                    severity: 'warning',
                    file: $controller->relativePath,
                    message: sprintf(
                        '%s injects %d dependencies.',
                        $controller->relativePath,
                        $count
                    ),
                    impact: 'Too many dependencies indicate high coupling.',
                    recommendation: 'Extract business logic into dedicated services.',
                    score: 5,
                    extra: [
                        'dependencies' => $count,
                    ]
                );
            }
        }

        return $issues;
    }
}
