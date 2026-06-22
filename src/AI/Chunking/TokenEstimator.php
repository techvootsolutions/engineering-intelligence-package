<?php

namespace Dev\EipAgent\AI\Chunking;

class TokenEstimator
{
    /**
     * Estimate the number of tokens in a payload using a simple heuristic.
     * 
     * @param mixed $payload
     * @return int
     */
    public function estimate(mixed $payload): int
    {
        $json = json_encode($payload);
        if ($json === false) {
            return 0;
        }

        return (int) ceil(mb_strlen($json) / 4);
    }

    /**
     * Check if a payload is within a safe token limit.
     * 
     * @param mixed $payload
     * @param int $safeLimit
     * @return bool
     */
    public function isWithinLimit(mixed $payload, int $safeLimit = 12000): bool
    {
        $actualLimit = $safeLimit * 0.8; // Use 80% as a safety buffer
        return $this->estimate($payload) <= $actualLimit;
    }
}
