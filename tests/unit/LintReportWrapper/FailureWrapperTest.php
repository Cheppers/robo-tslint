<?php

namespace Sweetchuck\Robo\TsLint\Tests\Unit\LintReportWrapper;

use Sweetchuck\LintReport\ReportWrapperInterface;
use Sweetchuck\Robo\TsLint\LintReportWrapper\FailureWrapper;
use Codeception\Test\Unit;

class FailureWrapperTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\TsLint\Test\UnitTester
     */
    protected $tester;

    public function casesAll(): array
    {
        return [
            'a' => [
                [
                    'severity' => ReportWrapperInterface::SEVERITY_WARNING,
                    'failure' => 'f1',
                    'name' => 'a.ts',
                    'ruleName' => 'r1',
                    'startPosition' => [
                        'character' => 1,
                        'line' => 2,
                        'position' => 3,
                    ],
                    'endPosition' => [
                        'character' => 4,
                        'line' => 5,
                        'position' => 6,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesAll
     */
    public function testAll(array $failure): void
    {
        $fw = new FailureWrapper($failure);
        $this->tester->assertEquals($failure['severity'], $fw->severity());
        $this->tester->assertEquals($failure['ruleName'], $fw->source());
        $this->tester->assertEquals($failure['failure'], $fw->message());
        $this->tester->assertEquals($failure['startPosition']['line'], $fw->line());
        $this->tester->assertEquals($failure['startPosition']['character'], $fw->column());
    }
}
