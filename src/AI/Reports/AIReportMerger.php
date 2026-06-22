<?php

namespace Dev\EipAgent\AI\Reports;

use Dev\EipAgent\AI\AIProviderInterface;
use Dev\EipAgent\AI\DTOs\AIAnalysisResult;

class AIReportMerger
{
    private ?AIProviderInterface $provider;

    public function __construct(?AIProviderInterface $provider = null)
    {
        $this->provider = $provider;
    }

    /**
     * Combine all individual AI chunk reports.
     * 
     * @param AIAnalysisResult[] $reports
     * @param bool $generateExecutiveSummary
     * @return string Final compiled report
     */
    public function merge(array $reports, bool $generateExecutiveSummary = true): string
    {
        $contentBlocks = [];
        
        foreach ($reports as $result) {
            $status = $result->success ? "✅ Success" : "❌ Failed";
            $block = "### Chunk ID: {$result->chunkId} [{$status}]\n\n";
            $block .= $result->content;
            $contentBlocks[] = $block;
        }
        
        $combinedContent = implode("\n\n---\n\n", $contentBlocks);

        $finalReport = "# EIP AI Engineering Intelligence Report\n\n";

        if ($generateExecutiveSummary && $this->provider) {
            $finalReport .= $this->generateExecutiveSummary($combinedContent) . "\n\n---\n\n";
        }

        $finalReport .= "## Detailed Analysis\n\n";
        $finalReport .= $combinedContent;

        return $finalReport;
    }

    private function generateExecutiveSummary(string $combinedContent): string
    {
        $prompt = "Generate a CTO-level engineering report from these findings.\n"
                . "Include:\n"
                . "- Strategic analysis\n"
                . "- Prioritization roadmap\n"
                . "- Executive summary\n\n"
                . "Findings:\n"
                . $combinedContent;
                
        // Fallback in case of failure or too many tokens
        try {
            $summary = $this->provider->analyze($prompt);
            return "## Executive Summary\n\n" . $summary;
        } catch (\Exception $e) {
            return "## Executive Summary\n\n*Failed to generate executive summary: " . $e->getMessage() . "*";
        }
    }
}
