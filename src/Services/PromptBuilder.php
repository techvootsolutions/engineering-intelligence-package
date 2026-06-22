<?php

namespace Dev\EipAgent\Services;

use Dev\EipAgent\DTOs\ScanResult;

class PromptBuilder
{
    /**
     * Build an AI prompt from the compressed AIContext payload.
     * This is the canonical method — receives only compressed, token-safe data.
     *
     * @param  array $context  Output of AIContextSerializer::buildContext()
     * @return string
     */
    public function buildFromContext(array $context): string
    {
        $project     = $context['project']      ?? 'Unknown Project';
        $projectType = $context['project_type'] ?? 'Laravel';
        $health      = $context['health_score'] ?? 0;
        $meta        = $context['metadata']     ?? [];
        $hotspots    = $context['hotspots']     ?? [];
        $chunks      = $context['chunks']       ?? [];

        $hotspotText = '';
        foreach (array_slice($hotspots, 0, 5) as $h) {
            $file = basename($h['file'] ?? 'unknown');
            $hotspotText .= "  - {$file} (risk: {$h['risk_score']}, issues: {$h['issue_count']})\n";
        }

        $chunksJson = json_encode($chunks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $tokenCount  = $meta['estimated_tokens']  ?? '?';
        $chunkCount  = $meta['chunk_count']        ?? count($chunks);
        $totalIssues = $meta['total_issues']       ?? '?';
        $compression = $meta['compression_ratio']  ?? '?';

        return <<<PROMPT
        You are a Senior {$projectType} Architect performing an engineering intelligence audit.

        PROJECT: {$project}
        FRAMEWORK: {$projectType}
        HEALTH SCORE: {$health}/100
        TOTAL ISSUES: {$totalIssues} (compressed to {$chunkCount} chunk(s), ~{$tokenCount} tokens, {$compression} compression)

        TOP RISK HOTSPOTS:
        {$hotspotText}
        COMPRESSED ISSUE CHUNKS (aggregated, de-duplicated):
        {$chunksJson}

        Generate a structured engineering intelligence report with these exact sections:

        ## Executive Summary
        (2-3 sentences: overall state, risk level, top concern)

        ## Architecture Analysis
        (key structural problems and their root causes)

        ## Performance Risks
        - (bullet list of performance issues)

        ## Security Risks
        - (bullet list of security issues)

        ## Recommendations
        - (prioritized action plan, most critical first)

        Be concise and precise. Focus on actionable insights. Output Markdown.
        PROMPT;
    }

    /**
     * @deprecated Use buildFromContext() instead.
     *             This method sends the full raw issue list causing token explosion.
     */
    public function build(ScanResult $scan): string
    {
        return <<<PROMPT
            You are a Senior Laravel Architect.

            Analyze the following Laravel project.

            Health Score:
            {$scan->health['health_score']}

            Issue Breakdown:
            {$this->formatArray($scan->issueBreakdown)}

            Metrics:
            {$this->formatArray($scan->metrics)}

            Risk Summary:
            {$this->formatArray($scan->summary)}

            Full Issue List:
            {$this->formatArray($scan->issues)}

            Generate:

            1. Executive Summary
            2. Architecture Risks
            3. Code Quality Findings
            4. Performance Findings
            5. Security Findings
            6. Prioritized Action Plan

            Output should be Markdown.
            PROMPT;
    }

    private function formatArray(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}