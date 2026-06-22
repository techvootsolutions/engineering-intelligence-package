<?php

namespace Dev\EipAgent\AI\Orchestration;

use Exception;
use Dev\EipAgent\AI\DTOs\ChunkData;

class ChunkRecoveryManager
{
    private int $maxRetries = 2;

    /**
     * Attempt to process a chunk, retrying on failure.
     * 
     * @param ChunkData $chunk
     * @param callable $processor
     * @return mixed
     * @throws Exception
     */
    public function processWithRecovery(ChunkData $chunk, callable $processor): mixed
    {
        $attempts = 0;
        $currentChunk = $chunk;

        while ($attempts <= $this->maxRetries) {
            try {
                return $processor($currentChunk);
            } catch (Exception $e) {
                $attempts++;
                
                if ($attempts > $this->maxRetries) {
                    throw new Exception("Chunk processing failed after {$this->maxRetries} retries: " . $e->getMessage(), 0, $e);
                }

                // If failed, try to reduce payload size by 20% by dropping issues
                // This is a naive reduction strategy. A more robust one might split the chunk.
                $currentChunk = $this->reducePayload($currentChunk);
            }
        }

        return null;
    }

    private function reducePayload(ChunkData $chunk): ChunkData
    {
        if (empty($chunk->payload['issues'])) {
            return $chunk;
        }

        $count = count($chunk->payload['issues']);
        $newCount = (int) max(1, floor($count * 0.8));
        
        $chunk->payload['issues'] = array_slice($chunk->payload['issues'], 0, $newCount);
        
        return $chunk;
    }
}
