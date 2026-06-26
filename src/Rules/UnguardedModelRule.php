<?php
namespace Techvoot\EIP\Rules;

class UnguardedModelRule extends BaseRule
{
    public function analyze(array $models): array
    {
        $issues = [];
        foreach ($models as $model) {
            if (preg_match('/protected\s+\$guarded\s*=\s*\[\];/i', $model->content)) {
                $issues[] = $this->issue(
                    type: 'unguarded_model',
                    file: $model->relativePath,
                    message: 'Model is totally unguarded ($guarded = []).',
                    impact: 'High risk of mass assignment vulnerabilities.',
                    recommendation: 'Specify $fillable properties explicitly.'
                );
            }
        }
        return $issues;
    }
}
