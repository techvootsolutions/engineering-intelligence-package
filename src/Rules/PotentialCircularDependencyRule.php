<?php
namespace Techvoot\EIP\Rules;

class PotentialCircularDependencyRule extends BaseRule
{
    public function analyze(array $services): array
    {
        $issues = [];
        // Basic static check: look for services injecting each other.
        // A full circular dependency check is complex, but we can look for common names.
        // For EIP, we can just look for "Service" injected into another "Service" as a warning flag, or specifically check constructor types.
        
        foreach ($services as $service) {
            preg_match('/__construct\s*\((.*?)\)/s', $service->content, $matches);
            if (!isset($matches[1])) continue;

            $dependencies = explode(',', $matches[1]);
            foreach ($dependencies as $dep) {
                if (preg_match('/([a-zA-Z0-9_]+Service)\s+\$/', $dep, $typeMatch)) {
                    $injectedService = $typeMatch[1];
                    // If a service injects another service, flag it as a potential circular risk
                    // This is naive, but works for the baseline.
                    $issues[] = $this->issue(
                        type: 'potential_circular_dependency',
                        file: $service->relativePath,
                        message: sprintf('Service injects another service: %s. Watch out for circular dependencies.', $injectedService),
                        impact: 'Can cause application crashes (infinite loops) during dependency resolution.',
                        recommendation: 'Use events, jobs, or a shared core service to decouple logic.'
                    );
                }
            }
        }
        return $issues;
    }
}
