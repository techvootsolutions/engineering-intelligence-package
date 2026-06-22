<?php

namespace Techvoot\EIP\AI\Orchestration;

use Techvoot\EIP\AI\AIProviderInterface;
use Techvoot\EIP\AI\Prompts\PromptManager;
use Techvoot\EIP\AI\DTOs\ChunkData;
use Techvoot\EIP\AI\DTOs\AIAnalysisResult;
use Techvoot\EIP\AI\Recovery\ChunkFailureManager;

class AIAnalysisOrchestrator
{
    private AIProviderInterface $provider;
    private PromptManager $promptManager;
    private ChunkRecoveryManager $recoveryManager;
    private ChunkFailureManager $failureManager;

    public function __construct(
        AIProviderInterface $provider,
        PromptManager $promptManager,
        ChunkRecoveryManager $recoveryManager,
        ChunkFailureManager $failureManager
    ) {
        $this->provider = $provider;
        $this->promptManager = $promptManager;
        $this->recoveryManager = $recoveryManager;
        $this->failureManager = $failureManager;
    }

    /**
     * Run chunk analyses independently.
     * 
     * @param ChunkData[] $chunks
     * @return AIAnalysisResult[] Array of analysis reports
     */
    public function orchestrate(array $chunks): array
    {
        $reports = [];

        foreach ($chunks as $chunk) {
            $reports[] = $this->analyzeChunk($chunk);
        }

        return $reports;
    }

    /**
     * Process a single chunk (designed for parallel readiness).
     * 
     * @param ChunkData $chunk
     * @return AIAnalysisResult
     */
    public function analyzeChunk(ChunkData $chunk): AIAnalysisResult
    {
        try {
            return $this->recoveryManager->processWithRecovery($chunk, function(ChunkData $currentChunk) {
                $prompt = $this->promptManager->buildFullPrompt($currentChunk->type, $currentChunk->payload);
                
                $content = $this->provider->analyze($prompt);
                
                return new AIAnalysisResult(
                    chunkId: $currentChunk->id,
                    provider: 'unknown', // Would pull from provider config
                    model: 'unknown',    // Would pull from provider config
                    success: true,
                    content: $content
                );
            });
        } catch (\Exception $e) {
            return $this->failureManager->handleFailure($chunk, $e);
        }
    }
}
