<?php
namespace Dev\EipAgent\Exporters;

use Dev\EipAgent\Contracts\ReportExporterInterface;
use Dev\EipAgent\DTOs\ScanResult;

class MarkdownExporter implements ReportExporterInterface
{
    public function export(ScanResult $result): string
    {
        $healthScore = $result->health['health_score'] ?? 0;
        $grade = $this->getGrade($healthScore);
        
        $criticalCount = count(array_filter($result->issues, fn($i) => ($i['severity'] ?? '') === 'critical'));
        $warningCount = count(array_filter($result->issues, fn($i) => ($i['severity'] ?? '') === 'warning'));
        
        $md = "# Engineering Intelligence Report\n\n";
        
        $md .= "## Executive Summary\n\n";
        $md .= "Engineering Intelligence scan completed for project: {$result->projectName}.\n\n";
        
        $md .= "---\n\n";
        $md .= "## Health Score\n\n";
        $md .= "| Metric | Value |\n";
        $md .= "| --- | --- |\n";
        $md .= "| Health Score | {$healthScore}/100 |\n";
        $md .= "| Grade | {$grade} |\n";
        $md .= "| Critical Issues | {$criticalCount} |\n";
        $md .= "| Warnings | {$warningCount} |\n\n";
        
        $md .= "---\n\n";
        
        $md .= $this->formatCategory($result->issues, 'architecture', 'Architecture Findings');
        $md .= $this->formatCategory($result->issues, 'security', 'Security Findings');
        $md .= $this->formatCategory($result->issues, 'performance', 'Performance Findings');
        $md .= $this->formatCategory($result->issues, 'laravel_standards', 'Laravel Standards Violations');
        
        $md .= $this->formatHighRiskFiles($result->issues);
        $md .= $this->formatTechnicalDebt($result->issues);
        
        $md .= "## Prioritized Action Plan\n\n";
        if ($criticalCount > 0) {
            $md .= "### Priority 1\n\n";
            $md .= "* Fix Critical Issues\n\n";
        }
        if ($warningCount > 0) {
            $md .= "### Priority 2\n\n";
            $md .= "* Review Warnings\n\n";
        }
        $md .= "---\n\n";
        
        if ($result->aiReport) {
            $md .= "## AI Insights\n\n";
            $md .= $result->aiReport . "\n\n";
        }
        
        return $md;
    }
    
    private function formatCategory(array $issues, string $category, string $title): string
    {
        $filtered = array_filter($issues, fn($i) => ($i['category'] ?? '') === $category);
        if (empty($filtered)) {
            return '';
        }
        
        $md = "## {$title}\n\n";
        foreach ($filtered as $issue) {
            $typeFormatted = ucwords(str_replace('_', ' ', $issue['type']));
            $md .= "### {$typeFormatted}\n\n";
            if (!empty($issue['file'])) {
                $md .= "* {$issue['file']}\n";
            }
            if (!empty($issue['method'])) {
                $md .= "Method:\n* {$issue['method']}\n\n";
            }
            $md .= "Issue:\n* {$issue['message']}\n\n";
            
            if (!empty($issue['severity'])) {
                $md .= "Severity:\n* " . ucfirst($issue['severity']) . "\n\n";
            }
            
            if (!empty($issue['recommendation'])) {
                $md .= "Recommendation:\n* {$issue['recommendation']}\n\n";
            }
        }
        $md .= "---\n\n";
        return $md;
    }

    private function formatHighRiskFiles(array $issues): string
    {
        $fileRisk = [];
        foreach ($issues as $issue) {
            if (empty($issue['file'])) continue;
            if (!isset($fileRisk[$issue['file']])) {
                $fileRisk[$issue['file']] = ['critical' => 0, 'warning' => 0];
            }
            if (($issue['severity'] ?? '') === 'critical') {
                $fileRisk[$issue['file']]['critical']++;
            } elseif (($issue['severity'] ?? '') === 'warning') {
                $fileRisk[$issue['file']]['warning']++;
            }
        }
        
        if (empty($fileRisk)) return '';
        
        $md = "## High Risk Files\n\n";
        $md .= "| File | Risk Level |\n";
        $md .= "| --- | --- |\n";
        
        foreach ($fileRisk as $file => $risk) {
            $level = $risk['critical'] > 0 ? 'Critical' : ($risk['warning'] > 0 ? 'High' : 'Medium');
            if ($level !== 'Medium') {
                $md .= "| {$file} | {$level} |\n";
            }
        }
        $md .= "\n---\n\n";
        return $md;
    }

    private function formatTechnicalDebt(array $issues): string
    {
        if (empty($issues)) return '';
        $md = "## Technical Debt Indicators\n\n";
        $md .= "* Multiple issues found affecting maintainability\n\n";
        $md .= "---\n\n";
        return $md;
    }

    private function getGrade(int $score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }
}
