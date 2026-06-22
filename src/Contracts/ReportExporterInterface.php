<?php
namespace Dev\EipAgent\Contracts;

use Dev\EipAgent\DTOs\ScanResult;

interface ReportExporterInterface
{
    public function export(ScanResult $result, string $mode = 'summary'): string;

}
