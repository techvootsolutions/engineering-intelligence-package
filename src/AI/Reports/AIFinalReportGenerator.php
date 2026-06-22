<?php

namespace Dev\EipAgent\AI\Reports;

/**
 * AIFinalReportGenerator
 *
 * Converts the raw AI provider response (markdown text) into a
 * structured, typed JSON artifact stored in storage/app/eip/ai/.
 *
 * Strategy: store the full raw_analysis (markdown) alongside a
 * lightly-structured envelope so consumers can parse or display
 * either form without a second AI round-trip.
 */
class AIFinalReportGenerator
{
    /**
     * Build the structured AI final report payload.
     *
     * @param  string $rawAiResponse  The markdown/text response from the AI provider
     * @param  array  $contextMeta    Metadata from AIContextSerializer (tokens, chunks etc.)
     * @return array
     */
    public function generate(string $rawAiResponse, array $contextMeta = []): array
    {
        $sections = $this->parseSections($rawAiResponse);

        return [
            'executive_summary'    => $sections['executive_summary']    ?? '',
            'architecture_analysis'=> $sections['architecture_analysis'] ?? '',
            'performance_risks'    => $sections['performance_risks']    ?? [],
            'security_risks'       => $sections['security_risks']       ?? [],
            'recommendations'      => $sections['recommendations']      ?? [],
            'raw_analysis'         => $rawAiResponse,
            'context_metadata'     => $contextMeta,
            'generated_at'         => now()->toISOString(),
        ];
    }

    /**
     * Attempt to extract labelled sections from the AI markdown response.
     * Falls back gracefully if the AI didn't use expected headings.
     */
    private function parseSections(string $markdown): array
    {
        $sections = [];

        // Section heading patterns we look for (case-insensitive)
        $patterns = [
            'executive_summary'     => '/#+\s*(executive\s+summary)[^\n]*\n(.*?)(?=#+|\z)/si',
            'architecture_analysis' => '/#+\s*(architecture[^\n]*)\n(.*?)(?=#+|\z)/si',
            'performance_risks'     => '/#+\s*(performance[^\n]*)\n(.*?)(?=#+|\z)/si',
            'security_risks'        => '/#+\s*(security[^\n]*)\n(.*?)(?=#+|\z)/si',
            'recommendations'       => '/#+\s*(recommendations?|action\s+plan|prioriti[sz]ed)[^\n]*\n(.*?)(?=#+|\z)/si',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $markdown, $m)) {
                $content = trim($m[2] ?? '');

                // For list-style sections, split into an array of items
                if (in_array($key, ['performance_risks', 'security_risks', 'recommendations'])) {
                    $sections[$key] = $this->extractListItems($content);
                } else {
                    $sections[$key] = $content;
                }
            }
        }

        return $sections;
    }

    /**
     * Extract markdown bullet/numbered list items into a plain string array.
     */
    private function extractListItems(string $text): array
    {
        $lines = explode("\n", $text);
        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);
            // Match: "- item", "* item", "1. item", "2) item"
            if (preg_match('/^(?:[-*]|\d+[.)]) +(.+)/', $line, $m)) {
                $items[] = trim($m[1]);
            }
        }

        // Fallback: if no list found, return the full block as one item
        return $items ?: (trim($text) !== '' ? [trim($text)] : []);
    }
}
