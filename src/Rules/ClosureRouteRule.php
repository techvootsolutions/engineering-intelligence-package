<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\DTOs\FileResult;

class ClosureRouteRule extends BaseRule
{
    /**
     * Detect route files that use Closure-based route handlers.
     * Closures prevent Laravel from caching the route list with `php artisan route:cache`.
     *
     * @param FileResult[] $routes
     */
    public function analyze(array $routes): array
    {
        $issues = [];

        foreach ($routes as $route) {
            if (preg_match('/Route::(?:get|post|put|patch|delete|any)\([\'"][^\'"]+[\'"]\s*,\s*(?:function\s*\(|fn\s*\()/', $route->content)) {
                $issues[] = $this->issue(
                    type: 'closure_route_detected',
                    severity: 'info',
                    file: $route->relativePath,
                    message: "One or more routes use a Closure as their handler.",
                    impact: "Closure-based routes prevent 'php artisan route:cache' from running, which can significantly slow down production request routing.",
                    recommendation: "Move all Closure logic into a dedicated Controller method and reference it as [MyController::class, 'method'].",
                    score: 5
                );
            }
        }

        return $issues;
    }
}
