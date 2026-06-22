<?php

namespace Dev\EipAgent\Analyzers;

use Dev\EipAgent\DTOs\FileResult;

abstract class BaseAnalyzer
{
    /**
     * @param FileResult[] $files
     * @return \Dev\EipAgent\DTOs\Issue[]
     */
    abstract public function analyze(array $files): array;

    /**
     * @return int
     */
    abstract public function getRulesCount(): int;
}
