<?php

namespace Sweetchuck\Robo\TsLint\Tests\Unit\Task;

use Sweetchuck\AssetJar\AssetJar;
use Sweetchuck\Robo\TsLint\Task\Run as RunTask;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Helper\Dummy\Output as DummyOutput;
use Helper\Dummy\Process as DummyProcess;
use Robo\Robo;

class RunTest extends Unit
{
    protected static function getMethod(string $name): \ReflectionMethod
    {
        $class = new \ReflectionClass(RunTask::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        DummyProcess::reset();
    }

    public function testGetSetLintReporters(): void
    {
        $task = new RunTask([
            'lintReporters' => [
                'aKey' => 'aValue',
            ],
        ]);

        $task
            ->addLintReporter('bKey', 'bValue')
            ->addLintReporter('cKey', 'cValue')
            ->removeLintReporter('bKey');

        $this->assertEquals(
            [
                'aKey' => 'aValue',
                'cKey' => 'cValue',
            ],
            $task->getLintReporters()
        );
    }

    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                'node_modules/.bin/tslint',
                [],
                [],
            ],
            'basic-tslint' => [
                'my-tslint',
                ['tslintExecutable' => 'my-tslint'],
                [],
            ],
            'basic-wd' => [
                "cd 'foo' && node_modules/.bin/tslint",
                ['workingDirectory' => 'foo'],
                [],
            ],
            'configFile-empty' => [
                "node_modules/.bin/tslint",
                ['configFile' => ''],
                [],
            ],
            'configFile-string' => [
                "node_modules/.bin/tslint --config 'foo'",
                ['configFile' => 'foo'],
                [],
            ],
            'exclude-string' => [
                "node_modules/.bin/tslint --exclude 'foo'",
                ['exclude' => 'foo'],
                [],
            ],
            'exclude-vector' => [
                "node_modules/.bin/tslint --exclude 'foo' --exclude 'bar' --exclude 'baz'",
                ['exclude' => ['foo', 'bar', 'baz']],
                [],
            ],
            'exclude-assoc' => [
                "node_modules/.bin/tslint --exclude 'a' --exclude 'd'",
                [
                    'exclude' => [
                        'a' => true,
                        'b' => null,
                        'c' => false,
                        'd' => true,
                        'e' => false,
                    ]
                ],
                [],
            ],
            'force-false' => [
                "node_modules/.bin/tslint",
                ['force' => false],
                [],
            ],
            'force-true' => [
                "node_modules/.bin/tslint --force",
                ['force' => true],
                [],
            ],
            'out-empty' => [
                "node_modules/.bin/tslint",
                ['out' => false],
                [],
            ],
            'out-foo' => [
                "node_modules/.bin/tslint --out 'foo'",
                ['out' => 'foo'],
                [],
            ],
            'rulesDir-empty' => [
                "node_modules/.bin/tslint",
                ['rulesDir' => ''],
                [],
            ],
            'rulesDir-foo' => [
                "node_modules/.bin/tslint --rules-dir 'foo'",
                ['rulesDir' => 'foo'],
                [],
            ],
            'formattersDir-empty' => [
                "node_modules/.bin/tslint",
                ['formattersDir' => ''],
                [],
            ],
            'formattersDir-foo' => [
                "node_modules/.bin/tslint --formatters-dir 'foo'",
                ['formattersDir' => 'foo'],
                [],
            ],
            'format-empty' => [
                'node_modules/.bin/tslint',
                ['format' => ''],
                [],
            ],
            'format-foo' => [
                "node_modules/.bin/tslint --format 'foo'",
                ['format' => 'foo'],
                [],
            ],
            'project-empty' => [
                'node_modules/.bin/tslint',
                ['project' => ''],
                [],
            ],
            'project-foo' => [
                "node_modules/.bin/tslint --project 'foo'",
                ['project' => 'foo'],
                [],
            ],
            'typeCheck-false' => [
                "node_modules/.bin/tslint",
                ['typeCheck' => ''],
                [],
            ],
            'typeCheck-true' => [
                "node_modules/.bin/tslint --type-check",
                ['typeCheck' => true],
                [],
            ],
            'paths-empty' => [
                "node_modules/.bin/tslint",
                ['paths' => []],
                [],
            ],
            'paths-vector' => [
                "node_modules/.bin/tslint -- 'foo' 'bar' 'baz'",
                ['paths' => ['foo', 'bar', 'baz']],
                [],
            ],
            'paths-assoc' => [
                "node_modules/.bin/tslint -- 'a' 'd'",
                [
                    'paths' => [
                        'a' => true,
                        'b' => null,
                        'c' => false,
                        'd' => true,
                        'e' => false,
                    ]
                ],
                [],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options, array $paths): void
    {
        $tslint = new RunTask($options, $paths);
        static::assertEquals($expected, $tslint->getCommand());
    }

    public function testExitCodeConstants(): void
    {
        static::assertEquals(0, RunTask::EXIT_CODE_OK);
        static::assertEquals(1, RunTask::EXIT_CODE_WARNING);
        static::assertEquals(2, RunTask::EXIT_CODE_ERROR);
        static::assertEquals(3, RunTask::EXIT_CODE_INVALID);
    }

    public function casesGetTaskExitCode(): array
    {
        $o = RunTask::EXIT_CODE_OK;
        $w = RunTask::EXIT_CODE_WARNING;
        $e = RunTask::EXIT_CODE_ERROR;
        $u = 5;

        return [
            'never-000' => [$o, 'never', 0, 0, 0],
            'never-001' => [$o, 'never', 0, 0, 1],
            'never-002' => [$o, 'never', 0, 0, 2],
            'never-005' => [$u, 'never', 0, 0, 5],

            'never-010' => [$o, 'never', 0, 1, 0],
            'never-011' => [$o, 'never', 0, 1, 1],
            'never-012' => [$o, 'never', 0, 1, 2],
            'never-015' => [$u, 'never', 0, 1, 5],

            'never-100' => [$o, 'never', 1, 0, 0],
            'never-101' => [$o, 'never', 1, 0, 1],
            'never-102' => [$o, 'never', 1, 0, 2],
            'never-105' => [$u, 'never', 1, 0, 5],

            'never-110' => [$o, 'never', 1, 1, 0],
            'never-111' => [$o, 'never', 1, 1, 1],
            'never-112' => [$o, 'never', 1, 1, 2],
            'never-115' => [$u, 'never', 1, 1, 5],

            'warning-000' => [$o, 'warning', 0, 0, 0],
            'warning-001' => [$o, 'warning', 0, 0, 1],
            'warning-002' => [$o, 'warning', 0, 0, 2],
            'warning-005' => [$u, 'warning', 0, 0, 5],

            'warning-010' => [$w, 'warning', 0, 1, 0],
            'warning-011' => [$w, 'warning', 0, 1, 1],
            'warning-012' => [$w, 'warning', 0, 1, 2],
            'warning-015' => [$u, 'warning', 0, 1, 5],

            'warning-100' => [$e, 'warning', 1, 0, 0],
            'warning-101' => [$e, 'warning', 1, 0, 1],
            'warning-102' => [$e, 'warning', 1, 0, 2],
            'warning-105' => [$u, 'warning', 1, 0, 5],

            'warning-110' => [$e, 'warning', 1, 1, 0],
            'warning-111' => [$e, 'warning', 1, 1, 1],
            'warning-112' => [$e, 'warning', 1, 1, 2],
            'warning-115' => [$u, 'warning', 1, 1, 5],

            'error-000' => [$o, 'error', 0, 0, 0],
            'error-001' => [$o, 'error', 0, 0, 1],
            'error-002' => [$o, 'error', 0, 0, 2],
            'error-005' => [$u, 'error', 0, 0, 5],

            'error-010' => [$o, 'error', 0, 1, 0],
            'error-011' => [$o, 'error', 0, 1, 1],
            'error-012' => [$o, 'error', 0, 1, 2],
            'error-015' => [$u, 'error', 0, 1, 5],

            'error-100' => [$e, 'error', 1, 0, 0],
            'error-101' => [$e, 'error', 1, 0, 1],
            'error-102' => [$e, 'error', 1, 0, 2],
            'error-105' => [$u, 'error', 1, 0, 5],

            'error-110' => [$e, 'error', 1, 1, 0],
            'error-111' => [$e, 'error', 1, 1, 1],
            'error-112' => [$e, 'error', 1, 1, 2],
            'error-115' => [$u, 'error', 1, 1, 5],
        ];
    }

    /**
     * @dataProvider casesGetTaskExitCode
     */
    public function testGetTaskExitCode(
        int $expected,
        string $failOn,
        int $numOfErrors,
        int $numOfWarnings,
        int $exitCode
    ): void {
        /** @var RunTask $runTask */
        $runTask = Stub::construct(
            RunTask::class,
            [['failOn' => $failOn]],
            ['exitCode' => $exitCode]
        );

        static::assertEquals(
            $expected,
            static::getMethod('getTaskExitCode')->invokeArgs($runTask, [$numOfErrors, $numOfWarnings])
        );
    }

    public function casesRun(): array
    {
        $reportBase = [];

        $failureWarning = [
            'severity' => 'warning',
            'failure' => 'f1',
            'name' => 'a.ts',
            'ruleName' => 'r1',
            'startPosition' => [
                'character' => 11,
                'line' => 12,
                'position' => 13,
            ],
            'endPosition' => [
                'character' => 14,
                'line' => 15,
                'position' => 16,
            ],
        ];

        $failureError = [
            'severity' => 'error',
            'failure' => 'f2',
            'name' => 'b.ts',
            'ruleName' => 'r2',
            'startPosition' => [
                'character' => 21,
                'line' => 22,
                'position' => 23,
            ],
            'endPosition' => [
                'character' => 24,
                'line' => 25,
                'position' => 26,
            ],
        ];

        $label_pattern = '%d; failOn: %s; E: %d; W: %d; exitCode: %d; withJar: %s;';
        $cases = [];

        $combinations = [
            ['e' => true, 'w' => true, 'f' => 'never', 'c' => 0],
            ['e' => true, 'w' => false, 'f' => 'never', 'c' => 0],
            ['e' => false, 'w' => true, 'f' => 'never', 'c' => 0],
            ['e' => false, 'w' => false, 'f' => 'never', 'c' => 0],

            ['e' => true, 'w' => true, 'f' => 'warning', 'c' => 2],
            ['e' => true, 'w' => false, 'f' => 'warning', 'c' => 2],
            ['e' => false, 'w' => true, 'f' => 'warning', 'c' => 1],
            ['e' => false, 'w' => false, 'f' => 'warning', 'c' => 0],

            ['e' => true, 'w' => true, 'f' => 'error', 'c' => 2],
            ['e' => true, 'w' => false, 'f' => 'error', 'c' => 2],
            ['e' => false, 'w' => true, 'f' => 'error', 'c' => 0],
            ['e' => false, 'w' => false, 'f' => 'error', 'c' => 0],
        ];

        $i = 0;
        foreach ([true, false] as $withJar) {
            $withJarStr = $withJar ? 'true' : 'false';
            foreach ($combinations as $c) {
                $i++;
                $report = $reportBase;

                if ($c['e']) {
                    $report[] = $failureError;
                }

                if ($c['w']) {
                    $report[] = $failureWarning;
                }

                $label = sprintf($label_pattern, $i, $c['f'], $c['e'], $c['w'], $c['c'], $withJarStr);
                $cases[$label] = [
                    $c['c'],
                    [
                        'failOn' => $c['f'],
                    ],
                    $withJar,
                    json_encode($report)
                ];
            }
        }

        return $cases;
    }

    /**
     * This way cannot be tested those cases when the lint process failed.
     *
     * @dataProvider casesRun
     */
    public function testRun(
        int $exitCode,
        array $options,
        bool $withJar,
        string $expectedStdOutput
    ): void {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $mainStdOutput = new DummyOutput();

        $options += [
            'workingDirectory' => 'my-working-dir',
            'assetJarMapping' => ['report' => ['tsLintRun', 'report']],
            'format' => 'json',
        ];

        /** @var \Sweetchuck\Robo\TsLint\Task\Run $task */
        $task = Stub::construct(
            RunTask::class,
            [$options, []],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => $exitCode,
            'stdOutput' => $expectedStdOutput,
        ];

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $assetJar = null;
        if ($withJar) {
            $assetJar = new AssetJar();
            $task->setAssetJar($assetJar);
        }

        $result = $task->run();

        $this->tester->assertEquals(
            $exitCode,
            $result->getExitCode(),
            'Exit code'
        );

        if ($withJar) {
            /** @var \Sweetchuck\LintReport\ReportWrapperInterface $reportWrapper */
            $reportWrapper = $assetJar->getValue(['tsLintRun', 'report']);
            $this->tester->assertEquals(
                json_decode($expectedStdOutput, true),
                $reportWrapper->getReport(),
                'Output equals with jar'
            );
        } else {
            $this->tester->assertContains(
                $expectedStdOutput,
                $mainStdOutput->output,
                'Output equals without jar'
            );
        }
    }

    public function testRunFailed(): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $exitCode = 1;
        $expectedReport = [
            [
                'severity' => 'warning',
                'name' => 'a.ts',
            ],
        ];
        $expectedReportJson = json_encode($expectedReport);
        $options = [
            'workingDirectory' => 'my-working-dir',
            'assetJarMapping' => ['report' => ['tsLintRun', 'report']],
            'failOn' => 'warning',
            'format' => 'json',
        ];

        /** @var RunTask $task */
        $task = Stub::construct(
            RunTask::class,
            [$options, []],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => $exitCode,
            'stdOutput' => $expectedReportJson,
        ];

        $task->setConfig(Robo::config());
        $task->setLogger($container->get('logger'));
        $assetJar = new AssetJar();
        $task->setAssetJar($assetJar);

        $result = $task->run();

        $this->tester->assertEquals(
            $exitCode,
            $result->getExitCode(),
            'Exit code'
        );

        /** @var \Sweetchuck\LintReport\ReportWrapperInterface $reportWrapper */
        $reportWrapper = $assetJar->getValue(['tsLintRun', 'report']);
        $this->tester->assertEquals($expectedReport, $reportWrapper->getReport());
    }
}
