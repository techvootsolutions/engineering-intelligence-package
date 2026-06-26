<?php
namespace Techvoot\EIP\Rules;

class QueryInControllerRule extends BaseRule
{
    public function analyze(array $controllers): array
    {
        $issues = [];
        foreach ($controllers as $controller) {
            if (preg_match('/(::|->)where\(|(::|->)find\(|(::|->)first\(|(::|->)get\(/i', $controller->content)) {
                $issues[] = $this->issue(
                    type: 'query_in_controller',
                    file: $controller->relativePath,
                    message: 'Database query detected in controller.',
                    impact: 'Violates MVC pattern and makes testing harder.',
                    recommendation: 'Move database queries to a Repository or Service.'
                );
            }
        }
        return $issues;
    }
}
