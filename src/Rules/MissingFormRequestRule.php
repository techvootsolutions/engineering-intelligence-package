<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\Rules\BaseRule;

class MissingFormRequestRule extends BaseRule
{
    public function analyze(array $controllers): array
    {
        $issues = [];

        foreach ($controllers as $controller) {

            $content = file_get_contents(
                $controller->path
            );

            preg_match_all(
                '/function\s+([a-zA-Z0-9_]+)\s*\((.*?)\)/s',
                $content,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $methodName = $match[1];
                $parameters = $match[2];
                if (str_contains($parameters, 'Request $request')) {
                    $issues[] = $this->issue(
                        type: 'missing_form_request',
                        severity: 'warning',
                        file: $controller->relativePath,
                        message: "Method {$methodName}() uses generic Request.",
                        impact: 'Validation logic is mixed with controller logic.',
                        recommendation: 'Use FormRequest instead of Request.',
                        score: 5,
                        extra: [
                            'method' => $methodName,
                        ]
                    );
                }
            }
        }

        return $issues;
    }
}
