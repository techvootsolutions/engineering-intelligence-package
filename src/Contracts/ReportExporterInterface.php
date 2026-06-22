<?php
namespace Techvoot\EIP\Contracts;

use Techvoot\EIP\DTOs\ScanResult;

interface ReportExporterInterface
{
    public function export(ScanResult $result, string $mode = 'summary'): string;

}
