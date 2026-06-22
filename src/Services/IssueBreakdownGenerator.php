<?php

namespace Dev\EipAgent\Services;

class IssueBreakdownGenerator
{
    public function generate(array $issues): array
    {
        $breakdown = [];

        foreach ($issues as $issue) {

            $type = $issue['type'];

            if (! isset($breakdown[$type])) {
                $breakdown[$type] = 0;
            }

            $breakdown[$type]++;
        }

        return $breakdown;
    }
}