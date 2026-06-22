<?php

namespace Dev\EipAgent\AI\Chunking;

use Dev\EipAgent\AI\Prioritization\HotspotAnalyzer;
use Dev\EipAgent\AI\DTOs\ChunkData;

class AIChunkBuilder
{
    private TokenEstimator $tokenEstimator;
    private ChunkContextBuilder $contextBuilder;
    private HotspotAnalyzer $hotspotAnalyzer;

    public function __construct(
        TokenEstimator $tokenEstimator,
        ChunkContextBuilder $contextBuilder,
        HotspotAnalyzer $hotspotAnalyzer
    ) {
        $this->tokenEstimator = $tokenEstimator;
        $this->contextBuilder = $contextBuilder;
        $this->hotspotAnalyzer = $hotspotAnalyzer;
    }

    /**
     * Build AI Chunks from compressed, aggregated issues.
     * 
     * @param array $compressedIssues
     * @param int $safeTokenLimit
     * @return ChunkData[]
     */
    public function buildChunks(array $compressedIssues, int $safeTokenLimit = 12000): array
    {
        $chunks = [];
        
        // Group by category
        $byCategory = [];
        foreach ($compressedIssues as $issue) {
            $cat = $issue['category'] ?? 'general';
            $byCategory[$cat][] = $issue;
        }

        $chunkCounter = 1;

        foreach ($byCategory as $category => $issues) {
            // Sort issues by priority (this would require having priority inside $compressedIssues or calculating it)
            // For simplicity, we just chunk them sequentially for now
            $currentBatch = [];
            
            foreach ($issues as $issue) {
                $testBatch = array_merge($currentBatch, [$issue]);
                
                // Construct a test payload to check tokens
                $testPayload = $this->contextBuilder->build($category, $testBatch);
                
                if (!$this->tokenEstimator->isWithinLimit($testPayload, $safeTokenLimit) && !empty($currentBatch)) {
                    // Flush current batch
                    $chunks[] = $this->finalizeChunk($category, $currentBatch, $chunkCounter, $safeTokenLimit);
                    $chunkCounter++;
                    $currentBatch = [$issue];
                } else {
                    $currentBatch[] = $issue;
                }
            }

            if (!empty($currentBatch)) {
                $chunks[] = $this->finalizeChunk($category, $currentBatch, $chunkCounter, $safeTokenLimit);
                $chunkCounter++;
            }
        }

        return $chunks;
    }

    private function finalizeChunk(string $category, array $issues, int $index, int $safeLimit): ChunkData
    {
        // Compute summary
        $summary = [
            'total_issues' => array_sum(array_column($issues, 'occurrences')),
            'unique_types' => count(array_unique(array_column($issues, 'type'))),
        ];

        // We can pass empty hotspots or calculate them based on the current chunk's issues
        $hotspots = [];

        $chunkId = "{$category}_" . str_pad((string)$index, 3, '0', STR_PAD_LEFT);
        $metadata = [
            'chunk_id'         => $chunkId,
            'priority_score'   => 0, // In a real scenario, sum priorities here
        ];

        $payload = $this->contextBuilder->build($category, $issues, $metadata, $summary, $hotspots);
        $estimatedTokens = $this->tokenEstimator->estimate($payload);
        $payload['chunk_metadata']['estimated_tokens'] = $estimatedTokens;

        return new ChunkData(
            id: $chunkId,
            type: $category,
            priority: 0,
            estimatedTokens: $estimatedTokens,
            payload: $payload,
            sourceFiles: $payload['traceability']['source_files'] ?? [],
            schemaVersion: $payload['schema_version'] ?? '1.0',
            metadata: $metadata
        );
    }
}
