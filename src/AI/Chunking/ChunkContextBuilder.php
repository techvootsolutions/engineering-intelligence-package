<?php

namespace Dev\EipAgent\AI\Chunking;

class ChunkContextBuilder
{
    private string $projectName;
    private string $framework;
    private string $schemaVersion = '1.0';

    public function __construct(string $projectName = 'EIP App', string $framework = 'Laravel')
    {
        $this->projectName = $projectName;
        $this->framework = $framework;
    }

    /**
     * @param string $chunkType e.g., 'architecture', 'security'
     * @param array $issues
     * @param array $metadata e.g., ['chunk_id' => ..., 'priority_score' => ..., 'estimated_tokens' => ...]
     * @param array $summary
     * @param array $hotspots
     * @return array
     */
    public function build(
        string $chunkType,
        array $issues,
        array $metadata = [],
        array $summary = [],
        array $hotspots = []
    ): array {
        // Extract unique source files for traceability
        $sourceFiles = array_values(array_unique(array_column($issues, 'file')));

        return [
            'schema_version' => $this->schemaVersion,
            'project'        => $this->projectName,
            'framework'      => $this->framework,
            'chunk_metadata' => array_merge([
                'chunk_type'   => $chunkType,
            ], $metadata),
            'traceability'   => [
                'source_files' => $sourceFiles,
            ],
            'summary'        => $summary,
            'hotspots'       => $hotspots,
            'issues'         => $issues,
        ];
    }
}
