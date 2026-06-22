<?php
namespace Dev\EipAgent\Rules;

use Dev\EipAgent\DTOs\FileResult;

class MassAssignmentRule extends BaseRule
{
    /**
     * Check if Model classes define $fillable or $guarded.
     * Missing both properties leaves the model open to mass-assignment attacks.
     *
     * @param FileResult[] $models
     */
    public function analyze(array $models): array
    {
        $issues = [];

        foreach ($models as $model) {
            $content = $model->content;

            // Only check actual Eloquent models
            if (!str_contains($content, 'extends Model') && !str_contains($content, 'extends Authenticatable')) {
                continue;
            }

            if (!str_contains($content, '$fillable') && !str_contains($content, '$guarded')) {
                $issues[] = $this->issue(
                    type: 'mass_assignment_vulnerability',
                    severity: 'high',
                    file: $model->relativePath,
                    message: "Model does not define \$fillable or \$guarded. This leaves all columns open to mass assignment.",
                    impact: "Attackers may be able to overwrite protected database columns via unfiltered request data.",
                    recommendation: "Add a protected \$fillable array listing the allowed columns, or use \$guarded = [] with caution.",
                    score: 20
                );
            }
        }

        return $issues;
    }
}
