<?php
namespace Dev\EipAgent\Exporters;

use Dev\EipAgent\Contracts\ReportExporterInterface;
use Dev\EipAgent\DTOs\ScanResult;

class JsonExporter implements ReportExporterInterface
{
    public function export(ScanResult $result): string
    {
        return json_encode(
            $result->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}
