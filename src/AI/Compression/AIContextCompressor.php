<?php

namespace Dev\EipAgent\AI\Compression;

class AIContextCompressor
{
    /**
     * Compress aggregated issues by removing redundant text and low-value data.
     * 
     * @param array $aggregatedIssues
     * @return array
     */
    public function compress(array $aggregatedIssues): array
    {
        $compressed = [];

        foreach ($aggregatedIssues as $issue) {
            // Skip trivial info level issues if they clutter context
            if ($issue['severity'] === 'info') {
                continue;
            }

            $compressedItem = [
                'type'        => $issue['type'],
                'file'        => $issue['file'],
                'occurrences' => $issue['occurrences'],
            ];

            if (!empty($issue['methods'])) {
                $compressedItem['methods'] = $issue['methods'];
            }

            // Only include message if there's no recommendation to save space,
            // or keep both but truncate. Usually, the type gives the AI enough context.
            if (!empty($issue['sample_recommendation'])) {
                $compressedItem['fix'] = $this->truncate($issue['sample_recommendation'], 200);
            } else {
                $compressedItem['msg'] = $this->truncate($issue['sample_message'], 200);
            }

            $compressed[] = $compressedItem;
        }

        return $compressed;
    }

    private function truncate(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit) . '...';
    }
}
