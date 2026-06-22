<?php
namespace Dev\EipAgent\Reporting;

use Dev\EipAgent\Contracts\ReportExporterInterface;
use Dev\EipAgent\DTOs\ScanResult;

/**
 * JsonReportGenerator
 *
 * Generates the raw JSON report artifact.
 * This is Layer 1 of the 3-layer architecture — full scan data for debugging.
 *
 * AI context → AIContextSerializer
 * AI final report → AIFinalReportGenerator
 */
class JsonReportGenerator implements ReportExporterInterface
{
    public function __construct(
        private ReportSerializer $serializer
    ) {}

    public function export(ScanResult $result, string $mode = 'summary'): string
    {
        $data = $this->serializer->serializeRaw($result);

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
