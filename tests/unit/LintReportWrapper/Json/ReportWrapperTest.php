<?php

namespace Cheppers\Robo\TsLint\Test\LintReportWrapper\Json;

use Cheppers\Robo\TsLint\LintReportWrapper\Json\ReportWrapper;

/**
 * Class ReportWrapperTest.
 */
class ReportWrapperTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function casesReports()
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
            'ok:one-file' => [
                'expected' => [
                    'countFiles' => 1,
                    'numOfErrors' => 0,
                    'numOfWarnings' => 0,
                    'highestSeverity' => 'ok',
                ],
                'report' => [
                    'a.ts' => [],
                ],
                'filesStats' => [
                    'a.ts' => [
                        'numOfErrors' => 0,
                        'numOfWarnings' => 0,
                        'highestSeverity' => 'ok',
                        'stats' => [
                            'severity' => 'ok',
                            'has' => [
                                'ok' => false,
                                'warning' => false,
                                'error' => false,
                            ],
                            'source' => [],
                        ],
                    ],
                ],
            ],
            'warning:one-file' => [
                'expected' => [
                    'countFiles' => 1,
                    'numOfErrors' => 0,
                    'numOfWarnings' => 1,
                    'highestSeverity' => 'warning',
                ],
                'report' => [
                    'a.ts' => [
                        [
                            'severity' => 'warning',
                            'source' => 's1',
                            'line' => 1,
                            'column' => 2,
                            'message' => 'm1',
                        ]
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
                                's1' => [
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
                    'a.ts' => [
                        [
                            'severity' => 'error',
                            'source' => 's1',
                            'line' => 1,
                            'column' => 2,
                            'message' => 'm1',
                        ]
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
                                's1' => [
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
     *
     * @param array $expected
     * @param array $report
     * @param array $filesStats
     */
    public function testAll(array $expected, array $report, array $filesStats)
    {
        $rw = new ReportWrapper($report);

        $this->tester->assertEquals($expected['countFiles'], $rw->countFiles());
        $this->tester->assertEquals($expected['numOfErrors'], $rw->numOfErrors());
        $this->tester->assertEquals($expected['numOfWarnings'], $rw->numOfWarnings());
        $this->tester->assertEquals($expected['highestSeverity'], $rw->highestSeverity());

        /**
         * @var string $filePath
         * @var \Cheppers\Robo\TsLint\LintReportWrapper\Json\FileWrapper $fw
         */
        foreach ($rw->yieldFiles() as $filePath => $fw) {
            $fileStats = $filesStats[$filePath];
            $this->tester->assertEquals($filePath, $fw->filePath());
            $this->tester->assertEquals($fileStats['numOfErrors'], $fw->numOfErrors());
            $this->tester->assertEquals($fileStats['numOfWarnings'], $fw->numOfWarnings());
            $this->tester->assertEquals($fileStats['highestSeverity'], $fw->highestSeverity());
            $this->tester->assertEquals($fileStats['stats'], $fw->stats());

            /**
             * @var int $i
             * @var \Cheppers\LintReport\FailureWrapperInterface $failureWrapper
             */
            foreach ($fw->yieldFailures() as $i => $failureWrapper) {
                $failure = $report[$filePath][$i];
                $this->tester->assertEquals($failure['severity'], $failureWrapper->severity());
                $this->tester->assertEquals($failure['source'], $failureWrapper->source());
                $this->tester->assertEquals($failure['line'], $failureWrapper->line());
                $this->tester->assertEquals($failure['column'], $failureWrapper->column());
                $this->tester->assertEquals($failure['message'], $failureWrapper->message());
            }
        }
    }
}
