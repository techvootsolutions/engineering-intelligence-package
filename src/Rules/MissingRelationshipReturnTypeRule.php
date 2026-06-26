<?php
namespace Techvoot\EIP\Rules;

class MissingRelationshipReturnTypeRule extends BaseRule
{
    public function analyze(array $models): array
    {
        $issues = [];
        foreach ($models as $model) {
            // Find methods that look like relationships (e.g. return $this->hasOne(...)) without a return type
            if (preg_match_all('/public function\s+([a-zA-Z0-9_]+)\s*\(\)\s*\{[^{}]*return\s+\$this->(hasOne|hasMany|belongsTo|belongsToMany|hasOneThrough|hasManyThrough|morphTo|morphOne|morphMany|morphToMany|morphedByMany)\s*\(/is', $model->content, $matches)) {
                foreach ($matches[1] as $method) {
                    $issues[] = $this->issue(
                        type: 'missing_relationship_return_type',
                        file: $model->relativePath,
                        message: sprintf('Relationship method "%s" is missing a return type.', $method),
                        impact: 'Reduces code readability and breaks IDE auto-completion.',
                        recommendation: 'Add the appropriate Eloquent relationship return type hint.'
                    );
                }
            }
        }
        return $issues;
    }
}
