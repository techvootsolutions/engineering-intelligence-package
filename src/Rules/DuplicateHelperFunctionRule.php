<?php
namespace Techvoot\EIP\Rules;

class DuplicateHelperFunctionRule extends BaseRule
{
    public function analyze(array $helpers): array
    {
        $issues = [];
        $functionsSeen = [];

        foreach ($helpers as $helper) {
            if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/', $helper->content, $matches)) {
                foreach ($matches[1] as $funcName) {
                    if (isset($functionsSeen[$funcName])) {
                        $issues[] = $this->issue(
                            type: 'duplicate_helper_function',
                            file: $helper->relativePath,
                            message: sprintf('Helper function "%s" might be duplicated (also seen in %s).', $funcName, $functionsSeen[$funcName]),
                            impact: 'Causes fatal errors (cannot redeclare function) if loaded globally without function_exists checks.',
                            recommendation: 'Wrap helpers in function_exists() and remove duplicates.'
                        );
                    } else {
                        $functionsSeen[$funcName] = $helper->relativePath;
                    }
                    
                    // Also check if wrapped in function_exists
                    $pattern = '/if\s*\(\s*!\s*function_exists\s*\(\s*[\'"]' . preg_quote($funcName, '/') . '[\'"]\s*\)\s*\)\s*\{\s*function\s+' . preg_quote($funcName, '/') . '/Uis';
                    if (!preg_match($pattern, $helper->content)) {
                        $issues[] = $this->issue(
                            type: 'duplicate_helper_function',
                            file: $helper->relativePath,
                            message: sprintf('Helper function "%s" is not wrapped in function_exists().', $funcName),
                            impact: 'Risk of redeclaration conflicts with vendor packages or other helpers.',
                            recommendation: 'Wrap the helper in if (!function_exists(...)).'
                        );
                    }
                }
            }
        }
        return $issues;
    }
}
