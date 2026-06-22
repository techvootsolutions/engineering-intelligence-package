<?php

namespace Dev\EipAgent\AI\Mappers;

use Dev\EipAgent\DTOs\Issue;
use Dev\EipAgent\AI\DTOs\IssueData;
use Dev\EipAgent\AI\Chunking\IssueCategoryClassifier;

class IssueMapper
{
    private IssueCategoryClassifier $classifier;

    public function __construct(IssueCategoryClassifier $classifier)
    {
        $this->classifier = $classifier;
    }

    public function map(Issue $issue): IssueData
    {
        $category = $this->classifier->classify($issue->type);

        $metadata = array_merge([
            'id'     => $issue->id,
            'line'   => $issue->line,
            'impact' => $issue->impact,
            'score'  => $issue->score,
        ], $issue->extra);

        return new IssueData(
            type: $issue->type,
            category: $category,
            severity: $issue->severity,
            file: $issue->file,
            method: $issue->method ?: null,
            message: $issue->message,
            recommendation: $issue->recommendation ?: null,
            metadata: $metadata
        );
    }

    /**
     * @param Issue[] $issues
     * @return IssueData[]
     */
    public function mapMany(array $issues): array
    {
        return array_map(fn(Issue $issue) => $this->map($issue), $issues);
    }
}
