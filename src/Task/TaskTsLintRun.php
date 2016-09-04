<?php

namespace Cheppers\Robo\TsLint\Task;

use Cheppers\AssetJar\AssetJarAware;
use Cheppers\AssetJar\AssetJarAwareInterface;
use Robo\Common\IO;
use Robo\Result;
use Robo\Task\BaseTask;
use Symfony\Component\Process\Process;

/**
 * Class TaskTsLintRun.
 *
 * Assert mapping:
 *   - report: Parsed JSON lint report.
 *
 * @package Cheppers\Robo\TsLint\Task
 */
class TaskTsLintRun extends BaseTask implements AssetJarAwareInterface
{

    use AssetJarAware;
    use IO;

    /**
     * Exit code: No lints were found.
     */
    const EXIT_CODE_OK = 0;

    /**
     * One or more errors were reported (and any number of warnings).
     */
    const EXIT_CODE_ERROR = 1;

    /**
     * @todo Some kind of dependency injection would be awesome.
     *
     * @var string
     */
    protected $processClass = Process::class;

    /**
     * @var string
     */
    protected $tslintExecutable = 'node_modules/.bin/tslint';

    /**
     * Directory to step in before run the `tslint`.
     *
     * @var string
     */
    protected $workingDirectory = '';

    /**
     * Severity level.
     *
     * @var bool
     */
    protected $failOn = 'error';

    /**
     * The location of the configuration file.
     *
     * @var string
     */
    protected $configFile = null;

    /**
     * A filename or glob which indicates files to exclude from linting.
     *
     * @var array
     */
    protected $exclude = [];

    /**
     * Return status code 0 even if there are any lint errors.
     *
     * @var string
     */
    protected $force = '';

    /**
     * A filename to output the results to.
     *
     * By default, tslint outputs to stdout, which is usually the console where
     * you're running it from.
     *
     * @var string
     */
    protected $out = '';

    /**
     *  An additional rules directory, for user-created rules.
     *
     * @var string
     */
    protected $rulesDir = '';

    /**
     * An additional formatters directory, for user-created formatters.
     *
     * @var string
     */
    protected $formattersDir = '';

    /**
     * The formatter to use to format the results.
     *
     * @var string
     */
    protected $format = '';

    /**
     * The location of a tsconfig.json file that will be used to determine which files will be linted.
     *
     * @var string
     */
    protected $project = '';

    /**
     * Enables the type checker when running linting rules.
     *
     * The --project must be specified in order to enable type checking.
     *
     * @var bool|null
     */
    protected $typeCheck = null;

    /**
     * TypeScript files to check.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * @var string
     */
    protected $convertFormatTo = '';

    /**
     * Process exit code.
     *
     * @var int
     */
    protected $exitCode = 0;

    /**
     * Exit code and error message mapping.
     *
     * @var string
     */
    protected $exitMessages = [
        0 => 'No lints were found',
        1 => 'One or more errors were reported (and any number of warnings)',
    ];

    /**
     * TaskTsLintRun constructor.
     *
     * @param array $options
     *   Key-value pairs of options.
     * @param array $paths
     *   File paths.
     */
    public function __construct(array $options = [], array $paths = [])
    {
        $this->options($options);
        $this->paths($paths);
    }

    /**
     * All in one configuration.
     *
     * @param array $options
     *   Options.
     *
     * @return $this
     */
    public function options(array $options)
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'assetJarMapping':
                    $this->setAssetJarMapping($value);
                    break;

                case 'workingDirectory':
                    $this->workingDirectory($value);
                    break;

                case 'failOn':
                    $this->failOn($value);
                    break;

                case 'configFile':
                    $this->configFile($value);
                    break;

                case 'exclude':
                    $this->exclude($value);
                    break;

                case 'force':
                    $this->force($value);
                    break;

                case 'out':
                    $this->out($value);
                    break;

                case 'rulesDir':
                    $this->rulesDir($value);
                    break;

                case 'formattersDir':
                    $this->formattersDir($value);
                    break;

                case 'format':
                    $this->format($value);
                    break;

                case 'project':
                    $this->project($value);
                    break;

                case 'typeCheck':
                    $this->typeCheck($value);
                    break;

                case 'paths':
                    $this->paths($value);
                    break;

                case 'convertFormatTo':
                    $this->convertFormatTo($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * Set the current working directory.
     *
     * @param string $value
     *   Directory path.
     *
     * @return $this
     */
    public function workingDirectory($value)
    {
        $this->workingDirectory = $value;

        return $this;
    }

    /**
     * Fail if there is a lint with warning severity.
     *
     * @param string $value
     *   Allowed values are: never, warning, error.
     *
     * @return $this
     */
    public function failOn($value)
    {
        $this->failOn = $value;

        return $this;
    }

    /**
     * Specify which configuration file you want to use.
     *
     * @param string $path
     *   File path.
     *
     * @return $this
     */
    public function configFile($path)
    {
        $this->configFile = $path;

        return $this;
    }

    /**
     * List of file names to exclude.
     *
     * @param string|string[]|bool[] $file_paths
     *   File names.
     * @param bool $include
     *   If TRUE $file_paths will be added to the exclude list.
     *
     * @return $this
     */
    public function exclude($file_paths, $include = true)
    {
        $this->exclude = $this->createIncludeList($file_paths, $include) + $this->exclude;

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function force($value)
    {
        $this->force = $value;

        return $this;
    }

    /**
     * Write output to a file instead of STDOUT.
     *
     * @param string|null $file_path
     *
     * @return $this
     */
    public function out($file_path)
    {
        $this->out = $file_path;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function rulesDir($value)
    {
        $this->rulesDir = $value;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function formattersDir($value)
    {
        $this->formattersDir = $value;

        return $this;
    }

    /**
     * Specify how to display lints.
     *
     * @param string $value
     *   Formatter identifier.
     *
     * @return $this
     */
    public function format($value)
    {
        $this->format = $value;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function project($value)
    {
        $this->project = $value;

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function typeCheck($value)
    {
        $this->typeCheck = $value;

        return $this;
    }

    /**
     * File paths to lint.
     *
     * @param string|string[]|bool[] $paths
     *   Key-value pair of file names and boolean.
     * @param bool $include
     *   Exclude or include the files in $paths.
     *
     * @return $this
     */
    public function paths(array $paths, $include = true)
    {
        $this->paths = $this->createIncludeList($paths, $include) + $this->paths;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function convertFormatTo($value)
    {
        $this->convertFormatTo = $value;

        return $this;
    }

    /**
     * The array key is the relevant value and the array value will be a boolean.
     *
     * @param string|string[]|bool[] $items
     *   Items.
     * @param bool $include
     *   Default value.
     *
     * @return bool[]
     *   Key is the relevant value, the value is a boolean.
     */
    protected function createIncludeList($items, $include)
    {
        if (!is_array($items)) {
            $items = [$items => $include];
        }

        $item = reset($items);
        if (gettype($item) !== 'boolean') {
            $items = array_fill_keys($items, $include);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $command = $this->buildCommand();

        $this->printTaskInfo(sprintf('TsLint task runs: <info>%s</info>', $command));

        /** @var Process $process */
        $process = new $this->processClass($command);
        if ($this->workingDirectory) {
            $process->setWorkingDirectory($this->workingDirectory);
        }

        $this->startTimer();
        $process->run();
        $this->stopTimer();

        $this->exitCode = $process->getExitCode();

        $write_output = true;

        $report_parents = $this->getAssetJarMap('report');
        if ($this->hasAssetJar()
            && $report_parents
            && !$this->out
            && $this->format === 'yaml'
            && $this->convertFormatTo === 'yaml2jsonGroupByFiles'
            && in_array($this->exitCode, $this->lintSuccessExitCodes())
        ) {
            $report = json_decode($process->getOutput(), true);
            if ($report) {
                $this->exitCode = static::EXIT_CODE_ERROR;
            }

            $write_output = false;
            $this
                ->getAssetJar()
                ->setValue($report_parents, $report);
        }

        if ($write_output) {
            $this->getOutput()->writeln($process->getOutput());
        }

        $message = isset($this->exitMessages[$this->exitCode]) ?
            $this->exitMessages[$this->exitCode]
            : $process->getErrorOutput();

        return new Result(
            $this,
            $this->getTaskExitCode(),
            $message,
            [
                'time' => $this->getExecutionTime(),
            ]
        );
    }

    /**
     * Build the CLI command based on the configuration.
     *
     * @return string
     *   CLI command to execute.
     */
    public function buildCommand()
    {
        $cmd_pattern = '%s';
        $cmd_args = [
            escapeshellcmd($this->tslintExecutable),
        ];

        if ($this->configFile) {
            $cmd_pattern .= ' --config %s';
            $cmd_args[] = escapeshellarg($this->configFile);
        }

        $exclude = array_keys($this->exclude, true, true);
        $cmd_pattern .= str_repeat(' --exclude %s', count($exclude));
        foreach ($exclude as $value) {
            $cmd_args[] = escapeshellarg($value);
        }

        if ($this->force) {
            $cmd_pattern .= ' --force';
        }

        if ($this->out && !$this->convertFormatTo) {
            $cmd_pattern .= ' --out %s';
            $cmd_args[] = escapeshellarg($this->out);
        }

        if ($this->rulesDir) {
            $cmd_pattern .= ' --rules-dir %s';
            $cmd_args[] = escapeshellarg($this->rulesDir);
        }

        if (!$this->formattersDir && $this->convertFormatTo) {
            $this->formattersDir = 'node_modules/tslint-formatters/lib/tslint/formatters';
        }

        if ($this->formattersDir) {
            $cmd_pattern .= ' --formatters-dir %s';
            $cmd_args[] = escapeshellarg($this->formattersDir);
        }

        if ($this->format) {
            $cmd_pattern .= ' --format %s';
            $cmd_args[] = escapeshellarg($this->format);
        }

        if ($this->project) {
            $cmd_pattern .= ' --project %s';
            $cmd_args[] = escapeshellarg($this->project);
        }

        if ($this->typeCheck) {
            $cmd_pattern .= ' --type-check';
        }

        $paths = array_keys($this->paths, true, true);
        if ($paths) {
            $cmd_pattern .= ' --' . str_repeat(' %s', count($paths));
            foreach ($paths as $path) {
                $cmd_args[] = escapeshellarg($path);
            }
        }

        if ($this->convertFormatTo) {
            // @todo Configurable node executable.
            // @todo Configurable tslint-formatters-convert executable.
            $cmd_pattern .= ' | node node_modules/.bin/tslint-formatters-convert %s';
            $cmd_args[] = escapeshellarg($this->convertFormatTo);

            if ($this->out) {
                $cmd_pattern .= ' --out %s';
                $cmd_args[] = escapeshellarg($this->out);
            }
        }

        return vsprintf($cmd_pattern, $cmd_args);
    }

    /**
     * Get the exit code regarding the failOn settings.
     *
     * @return int
     *   Exit code.
     */
    public function getTaskExitCode()
    {
        $tolerance = [
            'never' => [static::EXIT_CODE_ERROR,],
        ];

        if (isset($tolerance[$this->failOn]) && in_array($this->exitCode, $tolerance[$this->failOn])) {
            return static::EXIT_CODE_OK;
        }

        return $this->exitCode;
    }

    /**
     * @return int[]
     */
    protected function lintSuccessExitCodes()
    {
        return [
            static::EXIT_CODE_OK,
            static::EXIT_CODE_ERROR,
        ];
    }
}
