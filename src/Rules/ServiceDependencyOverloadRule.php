<?php
namespace Techvoot\EIP\Rules;

class ServiceDependencyOverloadRule extends BaseRule
{
    private int $maxDependencies;

    public function __construct(int $maxDependencies = 5)
    {
        $this->maxDependencies = $maxDependencies;
    }

    public function analyze(array $services): array
    {
        $issues = [];
        foreach ($services as $service) {
            preg_match('/__construct\s*\((.*?)\)/s', $service->content, $matches);
            if (!isset($matches[1])) continue;

            $dependencies = array_filter(array_map('trim', explode(',', $matches[1])));
            $count = count($dependencies);

            if ($count > $this->maxDependencies) {
                $issues[] = $this->issue(
                    type: 'service_dependency_overload',
                    file: $service->relativePath,
                    message: sprintf('Service injects %d dependencies.', $count),
                    impact: 'Too many dependencies indicate high coupling and a potential God class.',
                    recommendation: 'Break down the service into smaller, more focused classes.'
                );
            }
        }
        return $issues;
    }
}
