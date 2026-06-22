<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\DTOs\FileResult;

class ServiceGodClassRule extends BaseRule
{
    /**
     * Flag service classes that have grown too large (> 500 lines).
     * These classes almost certainly violate the Single Responsibility Principle.
     *
     * @param FileResult[] $services
     */
    public function analyze(array $services): array
    {
        $issues = [];

        foreach ($services as $service) {
            $lines = substr_count($service->content, "\n");

            if ($lines > 500) {
                $issues[] = $this->issue(
                    type: 'god_class_service',
                    severity: 'warning',
                    file: $service->relativePath,
                    message: "Service class is {$lines} lines long. Classes this large are difficult to maintain and test.",
                    impact: "A bloated service class is a strong indicator of poor separation of concerns, making the code harder to test, extend, and reason about.",
                    recommendation: "Break this class into smaller, focused action classes or dedicated sub-services.",
                    score: 15
                );
            }
        }

        return $issues;
    }
}
