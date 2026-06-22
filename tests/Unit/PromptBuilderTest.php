<?php
namespace Tests\Unit;

use Dev\EipAgent\DTOs\ScanResult;
use Dev\EipAgent\Services\PromptBuilder;
use PHPUnit\Framework\TestCase;

class PromptBuilderTest extends TestCase
{
    public function test_it_builds_a_structured_prompt()
    {
        $builder = new PromptBuilder();

        $scan = new ScanResult(
            projectName: 'Test App',
            projectType: 'project',
            rulesExecuted: 5,
            health: ['health_score' => 85],
            metrics: ['controllers' => 10],
            details: [],
            issues: [['type' => 'fat_controller']],
            issueBreakdown: ['fat_controller' => 1],
            summary: ['risk_level' => 'low']
        );

        $prompt = $builder->build($scan);

        $this->assertStringContainsString('Health Score:', $prompt);
        $this->assertStringContainsString('85', $prompt);
        $this->assertStringContainsString('Issue Breakdown:', $prompt);
        $this->assertStringContainsString('Metrics:', $prompt);
        $this->assertStringContainsString('Risk Summary:', $prompt);
        $this->assertStringContainsString('Full Issue List:', $prompt);
        $this->assertStringContainsString('1. Executive Summary', $prompt);
        $this->assertStringContainsString('2. Architecture Risks', $prompt);
        $this->assertStringContainsString('3. Code Quality Findings', $prompt);
        $this->assertStringContainsString('4. Performance Findings', $prompt);
        $this->assertStringContainsString('5. Security Findings', $prompt);
        $this->assertStringContainsString('6. Prioritized Action Plan', $prompt);
    }
}
