<?php
namespace Dev\EipAgent\Analyzers;

use Dev\EipAgent\Rules\MissingListenerRule;

class EventAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new MissingListenerRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $events = array_filter(
            $files,
            fn($file) => $file->classification === 'events'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($events)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
