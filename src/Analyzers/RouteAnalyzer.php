<?php
namespace Dev\EipAgent\Analyzers;

use Dev\EipAgent\Rules\ClosureRouteRule;

class RouteAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new ClosureRouteRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $routes = array_filter(
            $files,
            fn($file) => $file->classification === 'routes'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($routes)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
