<?php
namespace Tests\Unit;

use Dev\EipAgent\AI\AIManager;
use Dev\EipAgent\AI\AIProviderInterface;
use Dev\EipAgent\DTOs\ScanResult;
use Dev\EipAgent\Scanners\ProjectScanner;
use Dev\EipAgent\Services\ReportGenerator;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Mockery;

class ReportGeneratorTest extends TestCase
{
    private function makeScanResult(): ScanResult
    {
        return new ScanResult(
            projectName: 'Test App',
            projectType: 'project',
            rulesExecuted: 5,
            health: ['health_score' => 85],
            metrics: [],
            details: [],
            issues: [],
            issueBreakdown: [],
            summary: []
        );
    }

    public function test_it_generates_report_with_ai()
    {
        Config::set('eip.ai_enabled', true);

        $scannerMock = Mockery::mock(ProjectScanner::class);
        $scannerMock->shouldReceive('scan')->once()->andReturn($this->makeScanResult());

        $providerMock = Mockery::mock(AIProviderInterface::class);
        $providerMock->shouldReceive('generateReport')->once()->andReturn('Mocked AI Report');

        $aiManagerMock = Mockery::mock(AIManager::class);
        $aiManagerMock->shouldReceive('provider')->once()->andReturn($providerMock);

        $generator = new ReportGenerator($scannerMock, $aiManagerMock);
        $result = $generator->generate();

        $this->assertEquals('Mocked AI Report', $result->aiReport);
        $this->assertEquals('ai_enhanced', $result->scanType);
    }

    public function test_it_skips_ai_when_disabled()
    {
        Config::set('eip.ai_enabled', false);

        $scannerMock = Mockery::mock(ProjectScanner::class);
        $scannerMock->shouldReceive('scan')->once()->andReturn($this->makeScanResult());

        $aiManagerMock = Mockery::mock(AIManager::class);
        $aiManagerMock->shouldReceive('provider')->never();

        $generator = new ReportGenerator($scannerMock, $aiManagerMock);
        $result = $generator->generate();

        $this->assertNull($result->aiReport);
        $this->assertEquals('manual', $result->scanType);
    }

    public function test_it_populates_metadata_after_scan()
    {
        Config::set('eip.ai_enabled', false);

        $scannerMock = Mockery::mock(ProjectScanner::class);
        $scannerMock->shouldReceive('scan')->once()->andReturn($this->makeScanResult());

        $aiManagerMock = Mockery::mock(AIManager::class);
        $aiManagerMock->shouldReceive('provider')->never();

        $generator = new ReportGenerator($scannerMock, $aiManagerMock);
        $result = $generator->generate();

        $this->assertArrayHasKey('generated_at', $result->metadata);
        $this->assertArrayHasKey('scan_duration_ms', $result->metadata);
        $this->assertIsInt($result->metadata['scan_duration_ms']);
        $this->assertNotEmpty($result->metadata['generated_at']);
    }
}
