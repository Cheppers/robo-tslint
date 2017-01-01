<?php

namespace Cheppers\Robo\TsLint\Test\Unit\LintReportWrapper;

use Cheppers\Robo\TsLint\LintReportWrapper\ReportWrapper;
use Codeception\Test\Unit;

class ReportWrapperTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function casesReports(): array
    {
        return [
            'ok:no-files' => [
                'expected' => [
                    'countFiles' => 0,
                    'numOfErrors' => 0,
                    'numOfWarnings' => 0,
                    'highestSeverity' => 'ok',
                ],
                'report' => [],
                'filesStats' => [],
            ],
            'warning:one-file' => [
                'expected' => [
                    'countFiles' => 1,
                    'numOfErrors' => 0,
                    'numOfWarnings' => 1,
                    'highestSeverity' => 'warning',
                ],
                'report' => [
                    [
                        'name' => 'a.ts',
                        'severity' => 'warning',
                        'ruleName' => 'r1',
                        'failure' => 'f1',
                        'startPosition' => [
                            'line' => 1,
                            'character' => 2,
                            'position' => 3,
                        ],
                        'endPosition' => [
                            'line' => 4,
                            'character' => 5,
                            'position' => 6,
                        ],
                    ],
                ],
                'filesStats' => [
                    'a.ts' => [
                        'numOfErrors' => 0,
                        'numOfWarnings' => 1,
                        'highestSeverity' => 'warning',
                        'stats' => [
                            'severity' => 'warning',
                            'has' => [
                                'ok' => false,
                                'warning' => true,
                                'error' => false,
                            ],
                            'source' => [
                                'r1' => [
                                    'severity' => 'warning',
                                    'count' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'error:one-file' => [
                'expected' => [
                    'countFiles' => 1,
                    'numOfErrors' => 1,
                    'numOfWarnings' => 0,
                    'highestSeverity' => 'error',
                ],
                'report' => [
                    [
                        'name' => 'a.ts',
                        'severity' => 'error',
                        'ruleName' => 'r1',
                        'failure' => 'f1',
                        'startPosition' => [
                            'line' => 1,
                            'character' => 2,
                            'position' => 3,
                        ],
                        'endPosition' => [
                            'line' => 4,
                            'character' => 5,
                            'position' => 6,
                        ],
                    ],
                ],
                'filesStats' => [
                    'a.ts' => [
                        'numOfErrors' => 1,
                        'numOfWarnings' => 0,
                        'highestSeverity' => 'error',
                        'stats' => [
                            'severity' => 'error',
                            'has' => [
                                'ok' => false,
                                'warning' => false,
                                'error' => true,
                            ],
                            'source' => [
                                'r1' => [
                                    'severity' => 'error',
                                    'count' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesReports
     */
    public function testAll(array $expected, array $report, array $filesStats): void
    {
        $rw = new ReportWrapper($report);

        $this->tester->assertEquals($expected['countFiles'], $rw->countFiles());
        $this->tester->assertEquals($expected['numOfErrors'], $rw->numOfErrors());
        $this->tester->assertEquals($expected['numOfWarnings'], $rw->numOfWarnings());
        $this->tester->assertEquals($expected['highestSeverity'], $rw->highestSeverity());

        /**
         * @var string $filePath
         * @var \Cheppers\Robo\TsLint\LintReportWrapper\FileWrapper $fw
         */
        foreach ($rw->yieldFiles() as $filePath => $fw) {
            $fileStats = $filesStats[$filePath];
            $this->tester->assertEquals($filePath, $fw->filePath());
            $this->tester->assertEquals($fileStats['numOfErrors'], $fw->numOfErrors());
            $this->tester->assertEquals($fileStats['numOfWarnings'], $fw->numOfWarnings());
            $this->tester->assertEquals($fileStats['highestSeverity'], $fw->highestSeverity());
            $this->tester->assertEquals($fileStats['stats'], $fw->stats());
        }
    }
}
