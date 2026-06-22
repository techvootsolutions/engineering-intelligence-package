<?php

namespace Dev\EipAgent\Rules;

abstract class BaseRule
{
    protected function issue(
        string $type,
        string $severity,
        string $file,
        string $message,
        string $impact = '',
        string $recommendation = '',
        int $score = 0,
        array $extra = [],
        int $line = 0
    ): \Dev\EipAgent\DTOs\Issue {
        return new \Dev\EipAgent\DTOs\Issue(
            type: $type,
            severity: $severity,
            file: $file,
            message: $message,
            impact: $impact,
            recommendation: $recommendation,
            method: $extra['method'] ?? '',
            score: $score,
            extra: $extra,
            line: $line
        );
    }
}