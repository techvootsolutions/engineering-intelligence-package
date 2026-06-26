<?php
namespace Techvoot\EIP\Rules;

class ControllerDependencyOverloadRule extends BaseRule
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
            preg_match('/__construct\s*\((.*?)\)/s', $controller->content, $matches);
            if (!isset($matches[1])) continue;

            $dependencies = array_filter(array_map('trim', explode(',', $matches[1])));
            $count = count($dependencies);

            if ($count > $this->maxDependencies) {
                $issues[] = $this->issue(
                    type: 'controller_dependency_overload',
                    file: $controller->relativePath,
                    message: sprintf('Controller injects %d dependencies.', $count),
                    impact: 'Too many dependencies indicate the controller is doing too much.',
                    recommendation: 'Extract logic into dedicated action classes or grouped services.'
                );
            }
        }
        return $issues;
    }
}
