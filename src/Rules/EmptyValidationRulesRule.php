<?php
namespace Techvoot\EIP\Rules;

class EmptyValidationRulesRule extends BaseRule
{
    public function analyze(array $requests): array
    {
        $issues = [];
        foreach ($requests as $request) {
            if (preg_match('/public function rules\(\)\s*(?::\s*array\s*)?\{\s*return\s*\[\];\s*\}/is', $request->content)) {
                $issues[] = $this->issue(
                    type: 'empty_validation_rules',
                    file: $request->relativePath,
                    message: 'Form Request rules() returns an empty array.',
                    impact: 'No validation is being performed.',
                    recommendation: 'Define validation rules for the request.'
                );
            }
        }
        return $issues;
    }
}
