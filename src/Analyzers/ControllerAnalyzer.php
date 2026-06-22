<?php
namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\Rules\FatControllerRule;
use Techvoot\EIP\Rules\LongMethodRule;
use Techvoot\EIP\Rules\MissingFormRequestRule;
use Techvoot\EIP\Rules\MissingTransactionRule;
use Techvoot\EIP\Rules\PotentialNPlusOneRule;
use Techvoot\EIP\Rules\TooManyDependenciesRule;

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
