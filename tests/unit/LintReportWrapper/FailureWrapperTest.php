<?php

namespace Cheppers\Robo\TsLint\Test\Unit\LintReportWrapper;

use Cheppers\LintReport\ReportWrapperInterface;
use Cheppers\Robo\TsLint\LintReportWrapper\FailureWrapper;
use Codeception\Test\Unit;

class FailureWrapperTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function casesAll()
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
