<?php
namespace Techvoot\EIP\Rules;

class MissingStrictTypesRule extends BaseRule
{
    public function analyze(array $files): array
    {
        $issues = [];
        foreach ($files as $file) {
            if (str_ends_with($file->relativePath, '.php') && !str_contains($file->content, 'declare(strict_types=1);')) {
                $issues[] = $this->issue(
                    type: 'missing_strict_types',
                    file: $file->relativePath,
                    message: 'Missing declare(strict_types=1);',
                    impact: 'Increases risk of type-related bugs.',
                    recommendation: 'Add declare(strict_types=1); to the top of the file.'
                );
            }
        }
        return $issues;
    }
}
