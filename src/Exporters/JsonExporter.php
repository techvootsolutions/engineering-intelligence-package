<?php
namespace Techvoot\EIP\Exporters;

use Techvoot\EIP\Contracts\ReportExporterInterface;
use Techvoot\EIP\DTOs\ScanResult;

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
