<?php
namespace Dev\EipAgent\Analyzers;

use Dev\EipAgent\Rules\FatControllerRule;
use Dev\EipAgent\Rules\LongMethodRule;
use Dev\EipAgent\Rules\MissingFormRequestRule;
use Dev\EipAgent\Rules\MissingTransactionRule;
use Dev\EipAgent\Rules\PotentialNPlusOneRule;
use Dev\EipAgent\Rules\TooManyDependenciesRule;

class ControllerAnalyzer extends BaseAnalyzer
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new FatControllerRule(),
            new TooManyDependenciesRule(),
            new LongMethodRule(50),
            new MissingFormRequestRule(),
            new MissingTransactionRule(),
            new PotentialNPlusOneRule(),
        ];
    }

    public function analyze(array $files): array
    {
        $issues = [];

        $controllers = array_filter(
            $files,
            fn($file) => $file->classification === 'controllers'
        );

        foreach ($this->rules as $rule) {
            $issues = array_merge(
                $issues,
                $rule->analyze($controllers)
            );
        }

        return $issues;
    }

    public function getRulesCount(): int
    {
        return count($this->rules);
    }
}
