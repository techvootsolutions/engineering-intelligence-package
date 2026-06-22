<?php
namespace Tests\Unit;

use Dev\EipAgent\Services\HealthScoreCalculator;
use PHPUnit\Framework\TestCase;

class HealthScoreCalculatorTest extends TestCase
{
    public function test_it_calculates_perfect_score_when_no_issues()
    {
        $calculator = new HealthScoreCalculator();
        $health = $calculator->calculate([]);

        $this->assertEquals(100, $health['health_score']);
    }

    public function test_it_deducts_score_based_on_issues()
    {
        $calculator = new HealthScoreCalculator();
        $issues = [
            ['score' => 10],
            ['score' => 5],
        ];

        $health = $calculator->calculate($issues);

        $this->assertEquals(85, $health['health_score']);
    }
}
