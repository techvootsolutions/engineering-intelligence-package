<?php
namespace Dev\EipAgent\Reporting;

use Dev\EipAgent\AI\Reports\AIFinalReportGenerator;
use Dev\EipAgent\DTOs\ScanResult;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

/**
 * ReportManager
 *
 * Orchestrates file generation for the 3-layer architecture:
 *
 *   ALWAYS
 *     storage/app/eip/reports/eip-raw-{ts}.json      ← full scan artifact
 *
 *   IF AI ENABLED (aiContext present)
 *     storage/app/eip/context/eip-ai-context-{ts}.json ← compressed AI ingestion payload
 *     storage/app/eip/ai/eip-ai-report-{ts}.json       ← structured AI conclusions
 */
class ReportManager
{
    public function __construct(
        private JsonReportGenerator     $jsonGenerator,
        private MarkdownReportGenerator $markdownGenerator,
        private AIFinalReportGenerator  $aiFinalReportGenerator
    ) {}

    /**
     * @param ScanResult $result
     * @param array{json: bool, markdown: bool, export: bool, output: ?string} $options
     * @return string[]  Relative paths to generated files.
     */
    public function handle(ScanResult $result, array $options): array
    {
        $generatedFiles = [];
        $timestamp      = now()->format('Y-m-d-H-i-s');

        $wantsJson     = $options['json']     ?? false;
        $wantsMarkdown = $options['markdown'] ?? false;
        $wantsExport   = $options['export']   ?? false;
        $customBase    = $this->resolveCustomBase($options['output'] ?? null);

        if ($wantsExport) {
            $wantsJson     = true;
            $wantsMarkdown = true;
        }

        if (!$wantsJson && !$wantsMarkdown) {
            return $generatedFiles;
        }

        $eipBase = storage_path('app/eip');

        // ── Layer 1: Raw Report (ALWAYS) ──────────────────────────────────────
        $rawDir = $customBase ?? ($eipBase . '/reports');
        $this->ensureDir($rawDir);

        if ($wantsJson) {
            $path = $rawDir . "/eip-raw-{$timestamp}.json";
            File::put($path, $this->jsonGenerator->export($result));
            $generatedFiles[] = $this->formatPath($path);
        }

        if ($wantsMarkdown) {
            $path = $rawDir . "/eip-raw-{$timestamp}.md";
            File::put($path, $this->markdownGenerator->export($result));
            $generatedFiles[] = $this->formatPath($path);
        }

        // ── Layer 2 & 3: AI Layers (only when AI context was built) ───────────
        if (!empty($result->aiContext)) {
            if ($wantsJson) {
                // Layer 2: AI Context payload
                $contextDir = $customBase ?? ($eipBase . '/context');
                $this->ensureDir($contextDir);
                $path = $contextDir . "/eip-ai-context-{$timestamp}.json";
                File::put($path, json_encode($result->aiContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $generatedFiles[] = $this->formatPath($path);

                // Layer 3: AI Final Report
                if ($result->aiReport && !$result->aiScanFailed) {
                    $aiDir = $customBase ?? ($eipBase . '/ai');
                    $this->ensureDir($aiDir);
                    $path = $aiDir . "/eip-ai-report-{$timestamp}.json";
                    $aiReport = $this->aiFinalReportGenerator->generate(
                        $result->aiReport,
                        $result->aiContext['metadata'] ?? []
                    );
                    File::put($path, json_encode($aiReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    $generatedFiles[] = $this->formatPath($path);
                }
            }
        }

        return $generatedFiles;
    }

    private function resolveCustomBase(?string $outputPath): ?string
    {
        if (!$outputPath) {
            return null;
        }
        $resolved = Str::startsWith($outputPath, ['/', '\\'])
            ? $outputPath
            : base_path($outputPath);

        return Str::endsWith($resolved, ['.json', '.md'])
            ? dirname($resolved)
            : $resolved;
    }

    private function ensureDir(string $path): void
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    private function formatPath(string $absolutePath): string
    {
        $base = base_path();
        if (Str::startsWith($absolutePath, $base)) {
            return ltrim(Str::after($absolutePath, $base), '/\\');
        }
        return $absolutePath;
    }
}
