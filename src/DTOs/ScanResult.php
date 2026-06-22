<?php
namespace Dev\EipAgent\DTOs;

class ScanResult
{
    public function __construct(
        public string $projectName,
        public string $projectType,
        public int $rulesExecuted,
        public array $health,
        public array $metrics,
        public array $details,
        public array $issues,
        public array $issueBreakdown,
        public array $summary,
        public ?string $aiReport = null,
        public string $scanType = 'manual',
        public array $metadata = [],
        public array $groupedIssues = [],
        public array $hotspots = [],
        public bool $aiScanFailed = false,
        public array $aiContext = []   // Compressed AI ingestion payload — never in raw report
    ) {}

    public function toArray(): array
    {
        return [
            'scan_type'      => $this->scanType,
            'metadata'       => $this->metadata,
            'project_name'   => $this->projectName,
            'project_type'   => $this->projectType,
            'rules_executed' => $this->rulesExecuted,
            'health'         => $this->health,
            'summary'        => $this->summary,
            'issue_breakdown'=> $this->issueBreakdown,
            'metrics'        => $this->metrics,
            'details'        => $this->details,
            'issues'         => $this->issues,
            'grouped_issues' => $this->groupedIssues,
            'hotspots'       => $this->hotspots,
            'ai_report'      => $this->aiReport,
            'ai_scan_failed' => $this->aiScanFailed,
        ];
    }
}
