<?php

namespace Dev\EipAgent\AI\Recovery;

use Dev\EipAgent\AI\DTOs\ChunkData;
use Dev\EipAgent\AI\DTOs\AIAnalysisResult;

class ChunkFailureManager
{
    /**
     * Handle a complete chunk failure by generating a degraded AIAnalysisResult.
     * 
     * @param ChunkData $chunk
     * @param \Exception $exception
     * @param string $provider
     * @param string $model
     * @return AIAnalysisResult
     */
    public function handleFailure(
        ChunkData $chunk, 
        \Exception $exception, 
        string $provider = 'unknown', 
        string $model = 'unknown'
    ): AIAnalysisResult {
        return new AIAnalysisResult(
            chunkId: $chunk->id,
            provider: $provider,
            model: $model,
            success: false,
            content: json_encode([
                'summary' => 'Analysis failed for this section due to an error.',
                'risks' => [],
                'recommendations' => [],
                'technical_debt' => [],
                'priority' => 'low'
            ]),
            error: $exception->getMessage(),
            metadata: [
                'status' => ChunkStatus::FAILED->value,
                'chunk_type' => $chunk->type,
            ]
        );
    }
}
