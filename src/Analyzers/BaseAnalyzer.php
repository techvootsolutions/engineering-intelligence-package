<?php

namespace Techvoot\EIP\Analyzers;

use Techvoot\EIP\DTOs\FileResult;

abstract class BaseAnalyzer
{
    /**
     * @param FileResult[] $files
     * @return \Techvoot\EIP\DTOs\Issue[]
     */
    abstract public function analyze(array $files): array;

    /**
     * @return int
     */
    abstract public function getRulesCount(): int;
}
