<?php

namespace Techvoot\EIP\Rules;

abstract class BaseRule
{
    protected function issue(
        string $type,
        string $severity = '',
        string $file = '',
        string $message = '',
        string $impact = '',
        string $recommendation = '',
        int $score = 0,
        array $extra = [],
        int $line = 0
    ): \Techvoot\EIP\DTOs\Issue {
        $meta = \Techvoot\EIP\Rules\RuleRegistry::getRule($type);
        
        $overrides = config("eip.rule_overrides.{$type}", []);

        if (empty($severity)) {
            $severity = $overrides['severity'] ?? ($meta['severity'] ?? 'info');
        } else {
            $severity = $overrides['severity'] ?? $severity;
        }

        $confidence = $overrides['confidence'] ?? ($meta['confidence'] ?? 'medium');
        $ruleType = $meta['rule_type'] ?? 'heuristic';
        $category = $meta['category'] ?? 'quality';
        $title = $meta['title'] ?? ucwords(str_replace('_', ' ', $type));
        $note = $meta['note'] ?? '';
        $classification = $meta['classification'] ?? 'manual_review_required';
        $version = $meta['version'] ?? '1.0';

        return new \Techvoot\EIP\DTOs\Issue(
            type: $type,
            severity: $severity,
            file: $file,
            message: $message,
            impact: $impact,
            recommendation: $recommendation,
            method: $extra['method'] ?? '',
            score: $score,
            extra: $extra,
            line: $line,
            title: $title,
            confidence: $confidence,
            rule_type: $ruleType,
            category: $category,
            note: $note,
            classification: $classification,
            version: $version
        );
    }
}