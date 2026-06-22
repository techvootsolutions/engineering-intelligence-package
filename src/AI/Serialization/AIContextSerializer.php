<?php

namespace Dev\EipAgent\AI\Serialization;

use Dev\EipAgent\AI\Aggregation\IssueAggregator;
use Dev\EipAgent\AI\Chunking\AIChunkBuilder;
use Dev\EipAgent\AI\Chunking\IssueCategoryClassifier;
use Dev\EipAgent\AI\Chunking\TokenEstimator;
use Dev\EipAgent\AI\Compression\AIContextCompressor;
use Dev\EipAgent\AI\DTOs\IssueData;
use Dev\EipAgent\DTOs\ScanResult;

/**
 * AIContextSerializer
 *
 * Orchestrates the full AI context pipeline:
 *   ScanResult → IssueData[] → aggregate → compress → chunk → AIContext
 *
 * The resulting context is:
 *   - Token-safe (chunked, compressed)
 *   - AI-optimized (no redundant payloads)
 *   - Traceable (chunk IDs, source files)
 *
 * This is the ONLY data sent to the AI provider. Never the raw ScanResult.
 */
class AIContextSerializer
{
    public function __construct(
        private IssueAggregator         $aggregator,
        private AIContextCompressor     $compressor,
        private AIChunkBuilder          $chunkBuilder,
        private IssueCategoryClassifier $classifier,
        private TokenEstimator          $tokenEstimator
    ) {}

    /**
     * Build a compressed, token-safe AI context payload from a ScanResult.
     *
     * @return array{
     *   project: string,
     *   project_type: string,
     *   health_score: int,
     *   scan_type: string,
     *   chunks: array,
     *   metadata: array{
     *     chunk_count: int,
     *     estimated_tokens: int,
     *     total_issues: int,
     *     hotspot_count: int,
     *     compression_ratio: string
     *   }
     * }
     */
    public function buildContext(ScanResult $result): array
    {
        // ── 1. Map flat issues → typed IssueData DTOs with category classification ──
        $issueDtos = $this->mapToIssueDtos($result->issues);

        // ── 2. Aggregate: collapse duplicate type+file+severity combos ──
        $aggregated = $this->aggregator->aggregate($issueDtos);

        // ── 3. Compress: remove low-value fields, truncate long text ──
        $compressed = $this->compressor->compress($aggregated);

        // ── 4. Chunk: split into token-safe batches ──
        $safeTokenLimit = config('eip.ai_context_token_limit', 12000);
        $chunks = $this->chunkBuilder->buildChunks($compressed, $safeTokenLimit);

        // ── 5. Compute context metadata ──
        $totalTokens = array_sum(array_map(fn($c) => $c->estimatedTokens, $chunks));
        $rawPayloadSize = strlen(json_encode(array_column($result->issues, null)) ?: '');
        $compressedSize = strlen(json_encode($compressed) ?: '');
        $compressionRatio = $rawPayloadSize > 0
            ? round((1 - $compressedSize / $rawPayloadSize) * 100) . '%'
            : '0%';

        return [
            'project'      => $result->projectName,
            'project_type' => $result->projectType,
            'health_score' => $result->health['health_score'] ?? 0,
            'scan_type'    => $result->scanType,
            'hotspots'     => array_slice($result->hotspots, 0, 10), // Top 10 only
            'chunks'       => array_map(fn($c) => $c->toArray(), $chunks),
            'metadata'     => [
                'chunk_count'        => count($chunks),
                'estimated_tokens'   => $totalTokens,
                'total_issues'       => count($result->issues),
                'hotspot_count'      => count($result->hotspots),
                'compression_ratio'  => $compressionRatio,
            ],
        ];
    }

    /**
     * Map raw issue arrays → typed IssueData DTOs with category classification.
     *
     * @param  array[] $issues  Raw issue arrays from ScanResult
     * @return IssueData[]
     */
    private function mapToIssueDtos(array $issues): array
    {
        return array_map(function (array $issue): IssueData {
            $type = $issue['type'] ?? 'unknown';
            return new IssueData(
                type:           $type,
                category:       $this->classifier->classify($type),
                severity:       $issue['severity']       ?? 'info',
                file:           $issue['file']           ?? 'unknown',
                method:         $issue['method']         ?? null,
                message:        $issue['message']        ?? '',
                recommendation: $issue['recommendation'] ?? null,
                metadata:       ['id' => $issue['id'] ?? null, 'line' => $issue['line'] ?? 0],
            );
        }, $issues);
    }
}
