<?php

namespace Dev\EipAgent\Services;

use Dev\EipAgent\AI\AIManager;
use Dev\EipAgent\AI\Serialization\AIContextSerializer;
use Dev\EipAgent\DTOs\ScanResult;
use Dev\EipAgent\IssueOrganizer\HotspotCalculator;
use Dev\EipAgent\IssueOrganizer\IssueOrganizer;
use Dev\EipAgent\Scanners\ProjectScanner;
use Illuminate\Support\Facades\Log;

class ReportGenerator
{
    public function __construct(
        private ProjectScanner       $projectScanner,
        private AIManager            $aiManager,
        private AIContextSerializer  $aiContextSerializer,
        private IssueOrganizer       $issueOrganizer,
        private HotspotCalculator    $hotspotCalculator
    ) {}

    /**
     * Run the full 3-phase EIP pipeline:
     *
     *   Phase 1 — Static Scan   (always)
     *   Phase 2 — AI Context    (if ai_enabled: build compressed context from scan result)
     *   Phase 3 — AI Analysis   (if ai_enabled: send context to AI provider)
     *
     * The optional $onProgress callback lets the CLI report live step updates.
     * Signature: $onProgress(string $event, mixed $data)
     *
     * Events fired:
     *   - 'files_discovered'    — $data = int (file count)
     *   - 'analyzer_completed'  — $data = string (analyzer class name)
     *   - 'issues_aggregated'   — $data = int (total issue count)
     *   - 'hotspots_calculated' — $data = int (hotspot count)
     *   - 'context_built'       — $data = null
     *   - 'ai_completed'        — $data = null
     *   - 'reports_generated'   — $data = array (generated file paths)
     *
     * @param \Closure|null $onProgress  Optional progress event callback.
     * @return ScanResult  Fully populated result.
     */
    public function generate(?callable $onProgress = null): ScanResult
    {
        $startTime = microtime(true);

        // ── Phase 1: Static Scan ──────────────────────────────────────────────
        $result = $this->projectScanner->scan($onProgress);

        // Notify: all issues have been gathered from the static scan
        if ($onProgress) {
            $onProgress('issues_aggregated', count($result->issues));
        }

        // Build hotspots + type-aggregated grouped issues (always — needed for raw report)
        $groupedByFile        = $this->issueOrganizer->groupByFile($result->issues);
        $result->hotspots     = $this->hotspotCalculator->calculate($groupedByFile);
        $result->groupedIssues = $this->issueOrganizer->groupByTypeAndSummarize($result->issues);

        if ($onProgress) {
            $onProgress('hotspots_calculated', count($result->hotspots));
        }

        // ── Phase 2 & 3: AI Context + Analysis ───────────────────────────────
        if (config('eip.ai_enabled', true)) {
            try {
                // Phase 2: Build compressed, token-safe AI context
                $context          = $this->aiContextSerializer->buildContext($result);
                $result->aiContext = $context;

                if ($onProgress) {
                    $onProgress('context_built', null);
                }

                // Phase 3: Run AI on context (NOT on raw ScanResult)
                $provider         = $this->aiManager->provider();
                $aiText           = $provider->analyzeContext($context);
                $result->aiReport = $aiText;
                $result->scanType = 'ai_enhanced';

                if ($onProgress) {
                    $onProgress('ai_completed', null);
                }

            } catch (\Exception $e) {
                Log::warning("EIP AI analysis failed: " . $e->getMessage());
                $result->aiScanFailed = true;
                $result->aiReport     = 'AI analysis unavailable: ' . $e->getMessage();
                $result->scanType     = 'manual';

                if ($onProgress) {
                    $onProgress('ai_failed', $e->getMessage());
                }
            }
        }

        // ── Finalize metadata ─────────────────────────────────────────────────
        $durationMs         = (int) round((microtime(true) - $startTime) * 1000);
        $result->metadata   = array_merge($result->metadata ?? [], [
            'generated_at'     => now()->toISOString(),
            'scan_duration_ms' => $durationMs,
            'ai_enabled'       => config('eip.ai_enabled', true),
            'ai_context_tokens'=> $result->aiContext['metadata']['estimated_tokens'] ?? null,
        ]);

        return $result;
    }
}
