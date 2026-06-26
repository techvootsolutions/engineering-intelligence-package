<?php
namespace Techvoot\EIP\Rules;

class MissingAuthorizationCheckRule extends BaseRule
{
    public function analyze(array $controllers): array
    {
        $issues = [];
        foreach ($controllers as $controller) {
            // Check for modifying methods (store, update, destroy)
            if (preg_match('/public function (store|update|destroy)/i', $controller->content)) {
                if (!preg_match('/\$this->authorize\(|Gate::/i', $controller->content)) {
                    $issues[] = $this->issue(
                        type: 'missing_authorization_check',
                        file: $controller->relativePath,
                        message: 'Potential missing authorization check in controller modifying method.',
                        impact: 'Unauthorized users might be able to modify data.',
                        recommendation: 'Add $this->authorize() or Gate checks.'
                    );
                }
            }
        }
        return $issues;
    }
}
