<?php

namespace Dev\EipAgent\Reporting;

use Dev\EipAgent\DTOs\ScanResult;

/**
 * ReportSerializer
 *
 * Serializes a ScanResult into the raw report structure.
 * Raw reports contain the full scan output for debugging and developer inspection.
 *
 * AI context is handled by AIContextSerializer (separate concern).
 * AI final reports are handled by AIFinalReportGenerator (separate concern).
 */
class ReportSerializer
{
    /**
     * Serialize a ScanResult into the raw report array.
     * Intentionally excludes aiContext (internal pipeline artifact).
     */
    public function serializeRaw(ScanResult $result): array
    {
        return [
            'scan_type'       => $result->scanType,
            'metadata'        => $result->metadata,
            'project_name'    => $result->projectName,
            'project_type'    => $result->projectType,
            'rules_executed'  => $result->rulesExecuted,
            'health'          => $result->health,
            'summary'         => $result->summary,
            'issue_breakdown' => $result->issueBreakdown,
            'metrics'         => $result->metrics,
            'details'         => $result->details,
            'issues'          => $result->issues,
            'grouped_issues'  => $result->groupedIssues,
            'hotspots'        => $result->hotspots,
            // ai_report intentionally excluded from raw report — it lives in ai/ layer
        ];
    }
}
