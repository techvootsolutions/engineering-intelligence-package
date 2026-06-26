<?php
namespace Techvoot\EIP\Rules;

class RequestMissingAuthorizationRule extends BaseRule
{
    public function analyze(array $requests): array
    {
        $issues = [];
        foreach ($requests as $request) {
            if (preg_match('/public function authorize\(\)\s*(?::\s*bool\s*)?\{\s*return\s*true;\s*\}/is', $request->content)) {
                $issues[] = $this->issue(
                    type: 'request_missing_authorization',
                    file: $request->relativePath,
                    message: 'Form Request authorize() always returns true.',
                    impact: 'Bypasses authorization logic.',
                    recommendation: 'Implement proper authorization checks or rely on controller policies.'
                );
            }
        }
        return $issues;
    }
}
