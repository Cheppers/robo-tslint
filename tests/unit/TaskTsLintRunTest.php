<?php

use Cheppers\Robo\TsLint\Task\TaskTsLintRun;
use Codeception\Util\Stub;
use Robo\Robo;

/**
 * Class TaskTsLintRunTest.
 */
// @codingStandardsIgnoreStart
class TaskTsLintRunTest extends \Codeception\Test\Unit
{
    // @codingStandardsIgnoreEnd

    use Cheppers\Robo\TsLint\Task\LoadTasks;
    use \Robo\TaskAccessor;

    /**
     * @var \League\Container\Container
     */
    protected $container = null;

    // @codingStandardsIgnoreStart
    protected function _before()
    {
        // @codingStandardsIgnoreEnd
        $this->container = new \League\Container\Container();
        Robo::setContainer($this->container);
        \Robo\Runner::configureContainer($this->container, null, new \Helper\Dummy\Output());
        $this->container->addServiceProvider(Cheppers\Robo\TsLint\Task\LoadTasks::getTsLintServiceProvider());
    }

    /**
     * @return \League\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return array
     */
    public function casesBuildCommand()
    {
        return [
            'basic' => [
                'node_modules/.bin/tslint',
                [],
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
            'convertFormatTo-empty' => [
                "node_modules/.bin/tslint",
                ['convertFormatTo' => ''],
                [],
            ],
            'convertFormatTo-foo' => [
                implode(' ', [
                    'node_modules/.bin/tslint',
                    "--formatters-dir 'node_modules/tslint-formatters/lib/tslint/formatters'",
                    '--',
                    "'a'",
                    '|',
                    "node node_modules/.bin/tslint-formatters-convert 'yaml2jsonGroupByFiles'",
                ]),
                ['convertFormatTo' => 'yaml2jsonGroupByFiles'],
                ['a'],
            ],
            'convertFormatTo-foo-with-out' => [
                implode(' ', [
                    'node_modules/.bin/tslint',
                    "--formatters-dir 'node_modules/tslint-formatters/lib/tslint/formatters'",
                    '--',
                    "'a'",
                    '|',
                    "node node_modules/.bin/tslint-formatters-convert 'yaml2jsonGroupByFiles' --out 'b'",
                ]),
                [
                    'out' => 'b',
                    'convertFormatTo' => 'yaml2jsonGroupByFiles',
                ],
                ['a'],
            ],
        ];
    }

    /**
     * @dataProvider casesBuildCommand
     *
     * @param string $expected
     * @param array $options
     * @param array $paths
     */
    public function testBuildCommand($expected, array $options, array $paths)
    {
        $tslint = new TaskTsLintRun($options, $paths);
        static::assertEquals($expected, $tslint->buildCommand());
    }

    public function testExitCodeConstants()
    {
        static::assertEquals(0, TaskTsLintRun::EXIT_CODE_OK);
        static::assertEquals(1, TaskTsLintRun::EXIT_CODE_ERROR);
    }

    /**
     * @return array
     */
    public function casesGetTaskExitCode()
    {
        return [
            'never-ok' => [
                TaskTsLintRun::EXIT_CODE_OK,
                [
                    'failOn' => 'never',
                ],
                TaskTsLintRun::EXIT_CODE_OK,
            ],
            'never-error' => [
                TaskTsLintRun::EXIT_CODE_OK,
                [
                    'failOn' => 'never',
                ],
                TaskTsLintRun::EXIT_CODE_ERROR,
            ],
            'warning-ok' => [
                TaskTsLintRun::EXIT_CODE_OK,
                [
                    'failOn' => 'warning',
                ],
                TaskTsLintRun::EXIT_CODE_OK,
            ],
            'warning-error' => [
                TaskTsLintRun::EXIT_CODE_ERROR,
                [
                    'failOn' => 'warning',
                ],
                TaskTsLintRun::EXIT_CODE_ERROR,
            ],
            'error-ok' => [
                TaskTsLintRun::EXIT_CODE_OK,
                [
                    'failOn' => 'error',
                ],
                TaskTsLintRun::EXIT_CODE_OK,
            ],
            'error-error' => [
                TaskTsLintRun::EXIT_CODE_ERROR,
                [
                    'failOn' => 'error',
                ],
                TaskTsLintRun::EXIT_CODE_ERROR,
            ],
        ];
    }

    /**
     * @dataProvider casesGetTaskExitCode
     *
     * @param int $expected
     * @param array $options
     * @param int $exit_code
     */
    public function testGetTaskExitCode($expected, $options, $exit_code)
    {
        /** @var TaskTsLintRun $scss_lint */
        $scss_lint = Stub::construct(
            TaskTsLintRun::class,
            [$options, []],
            ['exitCode' => $exit_code]
        );

        static::assertEquals($expected, $scss_lint->getTaskExitCode());
    }

    /**
     * @return array
     */
    public function casesRun()
    {
        return [
            'without asset jar' => [
                0,
                'my-dummy-output',
                false,
            ],
            'with_asset_jar-success' => [
                0,
                [],
                true,
            ],
            'with_asset_jar-fail' => [
                1,
                ['file-01.ts' => []],
                true,
            ],
        ];
    }

    /**
     * This way cannot be tested those cases when the lint process failed.
     *
     * @dataProvider casesRun
     *
     * @param int $exit_code
     * @param string $std_output
     * @param bool $with_jar
     */
    public function testRun($exit_code, $std_output, $with_jar)
    {
        $options = [
            'workingDirectory' => 'my-working-dir',
            'assetJarMapping' => ['report' => ['tsLintRun', 'report']],
            'format' => 'yaml',
            'convertFormatTo' => 'yaml2jsonGroupByFiles',
        ];

        /** @var TaskTsLintRun $task */
        $task = Stub::construct(
            TaskTsLintRun::class,
            [$options, []],
            [
                'processClass' => \Helper\Dummy\Process::class,
            ]
        );

        \Helper\Dummy\Process::$exitCode = $exit_code;
        \Helper\Dummy\Process::$stdOutput = $with_jar ? json_encode($std_output) : $std_output;

        $task->setConfig(Robo::config());
        $task->setLogger($this->container->get('logger'));
        $asset_jar = null;
        if ($with_jar) {
            $asset_jar = new \Cheppers\AssetJar\AssetJar();
            $task->setAssetJar($asset_jar);
        }

        $result = $task->run();

        static::assertEquals($exit_code, $result->getExitCode());
        static::assertEquals(
            $options['workingDirectory'],
            \Helper\Dummy\Process::$instance->getWorkingDirectory()
        );

        if ($with_jar) {
            static::assertEquals($std_output, $asset_jar->getValue(['tsLintRun', 'report']));
        } else {
            /** @var \Helper\Dummy\Output $output */
            $output = $this->container->get('output');
            static::assertContains($std_output, $output->output);
        }
    }

    public function testRunFailed()
    {
        $exit_code = 1;
        $std_output = '{"foo": "bar"}';
        $options = [
            'workingDirectory' => 'my-working-dir',
            'assetJarMapping' => ['report' => ['tsLintRun', 'report']],
            'format' => 'yaml',
            'convertFormatTo' => 'yaml2jsonGroupByFiles',
        ];

        /** @var TaskTsLintRun $task */
        $task = Stub::construct(
            TaskTsLintRun::class,
            [$options, []],
            [
                'processClass' => \Helper\Dummy\Process::class,
            ]
        );

        \Helper\Dummy\Process::$exitCode = $exit_code;
        \Helper\Dummy\Process::$stdOutput = $std_output;

        $task->setConfig(Robo::config());
        $task->setLogger($this->container->get('logger'));
        $asset_jar = new \Cheppers\AssetJar\AssetJar();
        $task->setAssetJar($asset_jar);

        $result = $task->run();

        static::assertEquals($exit_code, $result->getExitCode());
        static::assertEquals(
            $options['workingDirectory'],
            \Helper\Dummy\Process::$instance->getWorkingDirectory()
        );

        static::assertEquals(['foo' => 'bar'], $asset_jar->getValue(['tsLintRun', 'report']));
    }

    public function testContainerInstance()
    {
        $task = $this->taskTsLintRun();
        static::assertEquals(0, $task->getTaskExitCode());
    }
}
