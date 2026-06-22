<?php
namespace Tests\Unit;

use Dev\EipAgent\DTOs\ScanResult;
use Dev\EipAgent\Reporting\JsonReportGenerator;
use Dev\EipAgent\Reporting\MarkdownReportGenerator;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    private function makeScanResult(array $overrides = []): ScanResult
    {
        $result = new ScanResult(
            projectName: 'Test App',
            projectType: 'project',
            rulesExecuted: 10,
            health: ['health_score' => 84],
            metrics: ['total_files' => 50],
            details: ['laravel_version' => '11.x'],
            issues: [
                [
                    'type' => 'missing_transaction',
                    'severity' => 'critical',
                    'category' => 'architecture',
                    'file' => 'app/Services/OrderService.php',
                    'method' => 'checkout',
                    'message' => 'Missing DB transaction',
                    'recommendation' => 'Wrap writes in DB::transaction()',
                ],
                [
                    'type' => 'n_plus_one',
                    'severity' => 'warning',
                    'category' => 'performance',
                    'file' => 'app/Http/Controllers/PostController.php',
                    'method' => null,
                    'message' => 'Potential N+1 query detected',
                    'recommendation' => 'Use eager loading with with()',
                ],
            ],
            issueBreakdown: ['critical' => 1, 'warning' => 1],
            summary: ['top_risks' => ['missing_transaction']],
            scanType: 'manual',
            metadata: [
                'generated_at' => '2026-06-08T06:42:15Z',
                'scan_duration_ms' => 1234,
            ]
        );

        foreach ($overrides as $key => $value) {
            $result->$key = $value;
        }

        return $result;
    }

    // --- JsonReportGenerator ---

    public function test_json_report_contains_required_top_level_keys()
    {
        $generator = new JsonReportGenerator();
        $json = $generator->export($this->makeScanResult());
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('scan_type', $data);
        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('health', $data);
        $this->assertArrayHasKey('issues', $data);
        $this->assertArrayHasKey('ai_report', $data);
    }

    public function test_json_report_metadata_has_correct_fields()
    {
        $generator = new JsonReportGenerator();
        $json = $generator->export($this->makeScanResult());
        $data = json_decode($json, true);

        $this->assertArrayHasKey('generated_at', $data['metadata']);
        $this->assertArrayHasKey('scan_duration_ms', $data['metadata']);
        $this->assertEquals(1234, $data['metadata']['scan_duration_ms']);
    }

    public function test_json_report_scan_type_is_manual_without_ai()
    {
        $generator = new JsonReportGenerator();
        $json = $generator->export($this->makeScanResult());
        $data = json_decode($json, true);

        $this->assertEquals('manual', $data['scan_type']);
        $this->assertNull($data['ai_report']);
    }

    public function test_json_report_scan_type_is_ai_enhanced_with_ai()
    {
        $result = $this->makeScanResult([
            'scanType' => 'ai_enhanced',
            'aiReport' => 'This is the AI summary.',
        ]);

        $generator = new JsonReportGenerator();
        $json = $generator->export($result);
        $data = json_decode($json, true);

        $this->assertEquals('ai_enhanced', $data['scan_type']);
        $this->assertEquals('This is the AI summary.', $data['ai_report']);
    }

    public function test_json_report_is_valid_json()
    {
        $generator = new JsonReportGenerator();
        $json = $generator->export($this->makeScanResult());

        $this->assertJson($json);
        $this->assertNotNull(json_decode($json));
    }

    // --- MarkdownReportGenerator ---

    public function test_markdown_report_contains_key_sections()
    {
        $generator = new MarkdownReportGenerator();
        $md = $generator->export($this->makeScanResult());

        $this->assertStringContainsString('# EIP Analysis Report', $md);
        $this->assertStringContainsString('## Project Health', $md);
        $this->assertStringContainsString('## Summary', $md);
        $this->assertStringContainsString('## Top Risks', $md);
        $this->assertStringContainsString('## Critical Issues', $md);
    }

    public function test_markdown_report_shows_health_score_and_grade()
    {
        $generator = new MarkdownReportGenerator();
        $md = $generator->export($this->makeScanResult());

        $this->assertStringContainsString('Score: 84', $md);
        $this->assertStringContainsString('Grade: B', $md);
    }

    public function test_markdown_report_shows_correct_issue_counts()
    {
        $generator = new MarkdownReportGenerator();
        $md = $generator->export($this->makeScanResult());

        $this->assertStringContainsString('1 critical issues', $md);
        $this->assertStringContainsString('1 warnings', $md);
    }

    public function test_markdown_report_contains_critical_issue_details()
    {
        $generator = new MarkdownReportGenerator();
        $md = $generator->export($this->makeScanResult());

        $this->assertStringContainsString('app/Services/OrderService.php', $md);
        $this->assertStringContainsString('Missing Transaction', $md);
    }

    public function test_markdown_report_includes_ai_section_when_present()
    {
        $result = $this->makeScanResult(['aiReport' => 'AI generated summary here.']);
        $generator = new MarkdownReportGenerator();
        $md = $generator->export($result);

        $this->assertStringContainsString('## AI Insights', $md);
        $this->assertStringContainsString('AI generated summary here.', $md);
    }

    public function test_markdown_report_excludes_ai_section_when_absent()
    {
        $generator = new MarkdownReportGenerator();
        $md = $generator->export($this->makeScanResult());

        $this->assertStringNotContainsString('## AI Insights', $md);
    }
}
