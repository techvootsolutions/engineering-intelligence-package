<?php

namespace Dev\EipAgent\AI;

use Dev\EipAgent\DTOs\ScanResult;

interface AIProviderInterface
{
    /**
     * @deprecated Use analyzeContext() instead. This sends the full raw scan
     *             to the AI which causes token explosion. Will be removed.
     */
    public function generateReport(ScanResult $scan): string;

    /**
     * Send a compressed AI context payload to the provider and return
     * the raw analysis text (markdown).
     *
     * @param  array $context  Output of AIContextSerializer::buildContext()
     * @return string          Raw AI response (markdown)
     */
    public function analyzeContext(array $context): string;

    /**
     * Analyze prompt using AI model.
     */
    public function analyze(string $prompt): string;
}