<?php

// @codingStandardsIgnoreStart
use Cheppers\Robo\TsLint\LintReportWrapper\Yaml\ReportWrapper;

/**
 * Class ReportWrapperTest.
 */
class YamlReportWrapperTest extends \Codeception\Test\Unit
{
    // @codingStandardsIgnoreEnd

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
            'warning:one-file' => [
                'expected' => [
                    'countFiles' => 1,
                    'numOfErrors' => 0,
                    'numOfWarnings' => 1,
                    'highestSeverity' => 'warning',
                ],
                'report' => [
                    [
                        'failures' => [
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
                            ]
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
                        'failures' => [
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
                            ]
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
     *
     * @param array $expected
     * @param array $report
     * @param array $filesStats
     */
    public function testAll(array $expected, array $report, array $filesStats)
    {
        $rw = new ReportWrapper($report);

        $this->assertEquals($expected['countFiles'], $rw->countFiles());
        $this->assertEquals($expected['numOfErrors'], $rw->numOfErrors());
        $this->assertEquals($expected['numOfWarnings'], $rw->numOfWarnings());
        $this->assertEquals($expected['highestSeverity'], $rw->highestSeverity());

        $docIndex = -1;
        /**
         * @var string $filePath
         * @var \Cheppers\Robo\TsLint\LintReportWrapper\Json\FileWrapper $fw
         */
        foreach ($rw->yieldFiles() as $fw) {
            $docIndex++;
            $filePath = $fw->filePath();
            $fileStats = $filesStats[$filePath];
            $this->assertEquals($filePath, $fw->filePath());
            $this->assertEquals($fileStats['numOfErrors'], $fw->numOfErrors());
            $this->assertEquals($fileStats['numOfWarnings'], $fw->numOfWarnings());
            $this->assertEquals($fileStats['highestSeverity'], $fw->highestSeverity());
            $this->assertEquals($fileStats['stats'], $fw->stats());

            /**
             * @var int $i
             * @var \Cheppers\LintReport\FailureWrapperInterface $failureWrapper
             */
            foreach ($fw->yieldFailures() as $i => $failureWrapper) {
                $failure = $report[$docIndex]['failures'][$i];
                $this->assertEquals($failure['severity'], $failureWrapper->severity());
                $this->assertEquals($failure['ruleName'], $failureWrapper->source());
                $this->assertEquals($failure['startPosition']['line'], $failureWrapper->line());
                $this->assertEquals($failure['startPosition']['character'], $failureWrapper->column());
                $this->assertEquals($failure['failure'], $failureWrapper->message());
            }
        }
    }
}
