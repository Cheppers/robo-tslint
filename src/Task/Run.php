<?php

namespace Sweetchuck\Robo\TsLint\Task;

use Sweetchuck\AssetJar\AssetJarAware;
use Sweetchuck\AssetJar\AssetJarAwareInterface;
use Sweetchuck\LintReport\ReporterInterface;
use Sweetchuck\LintReport\ReportWrapperInterface;
use Sweetchuck\Robo\TsLint\LintReportWrapper\ReportWrapper;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\OutputAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\CommandInterface;
use Robo\Contract\OutputAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Task\Filesystem\loadTasks as FsLoadTasks;
use Robo\Task\Filesystem\loadShortcuts as FsShortCuts;
use Robo\TaskAccessor;
use Symfony\Component\Process\Process;

/**
 * Class TaskTsLintRun.
 *
 * Assert mapping:
 *   - report: Parsed JSON lint report.
 *
 * @package Sweetchuck\Robo\TsLint\Task
 */
class Run extends BaseTask implements
    AssetJarAwareInterface,
    CommandInterface,
    ContainerAwareInterface,
    BuilderAwareInterface,
    OutputAwareInterface
{

    use AssetJarAware;
    use ContainerAwareTrait;
    use FsLoadTasks;
    use FsShortCuts;
    use OutputAwareTrait;
    use TaskAccessor;

    /**
     * Exit code: No lints were found.
     */
    const EXIT_CODE_OK = 0;

    /**
     * Lints with a severity of warning were reported (no errors).
     */
    const EXIT_CODE_WARNING = 1;

    /**
     * One or more errors were reported (and any number of warnings).
     */
    const EXIT_CODE_ERROR = 2;

    /**
     * Something is invalid.
     */
    const EXIT_CODE_INVALID = 3;

    /**
     * @todo Some kind of dependency injection would be awesome.
     *
     * @var string
     */
    protected $processClass = Process::class;

    //region Options.
    //region Option - workingDirectory.
    /**
     * @var string
     */
    protected $workingDirectory = '';

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * Directory to step in before run the `tslint`.
     *
     * @return $this
     */
    public function setWorkingDirectory(string $value)
    {
        $this->workingDirectory = $value;

        return $this;
    }
    //endregion

    //region Option - tslintExecutable.
    /**
     * @var string
     */
    protected $tslintExecutable = 'node_modules/.bin/tslint';

    public function getTslintExecutable(): string
    {
        return $this->tslintExecutable;
    }

    /**
     * @return $this
     */
    public function setTslintExecutable(string $tslintExecutable)
    {
        $this->tslintExecutable = $tslintExecutable;

        return $this;
    }
    //endregion

    //region Option - failOn.
    /**
     * Severity level.
     *
     * @var string
     */
    protected $failOn = 'error';

    public function getFailOn(): string
    {
        return $this->failOn;
    }

    /**
     * Fail if there is a lint with warning severity.
     *
     * @param string $value
     *   Allowed values are: never, warning, error.
     *
     * @return $this
     */
    public function setFailOn(string $value)
    {
        $this->failOn = $value;

        return $this;
    }
    //endregion

    //region Option - configFile.
    /**
     * @var string
     */
    protected $configFile = '';

    public function getConfigFile(): string
    {
        return $this->configFile;
    }

    /**
     * Specify which configuration file you want to use.
     *
     * @return $this
     */
    public function setConfigFile(string $path)
    {
        $this->configFile = $path;

        return $this;
    }
    //endregion

    //region Option - exclude.
    /**
     * A filename or glob which indicates files to exclude from linting.
     *
     * @var array
     */
    protected $exclude = [];

    public function getExclude(): array
    {
        return $this->exclude;
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
    public function setExclude($file_paths, $include = true)
    {
        $this->exclude = $this->createIncludeList($file_paths, $include) + $this->exclude;

        return $this;
    }
    //endregion

    //region Option - force.
    /**
     * Return status code 0 even if there are any lint errors.
     *
     * @var bool
     */
    protected $force = false;

    public function getForce(): bool
    {
        return $this->force;
    }

    /**
     * @return $this
     */
    public function setForce(bool $value)
    {
        $this->force = $value;

        return $this;
    }
    //endregion

    //region Option - out.
    /**
     * A filename to output the results to.
     *
     * By default, tslint outputs to stdout, which is usually the console where
     * you're running it from.
     *
     * @var string
     */
    protected $out = '';

    public function getOut(): string
    {
        return $this->out;
    }

    /**
     * Write output to a file instead of STDOUT.
     *
     * @return $this
     */
    public function setOut(string $filePath)
    {
        $this->out = $filePath;

        return $this;
    }
    //endregion

    //region Option - rulesDir.
    /**
     * @var string
     */
    protected $rulesDir = '';

    public function getRulesDir(): string
    {
        return $this->rulesDir;
    }

    /**
     * An additional rules directory, for user-created rules.
     *
     * @return $this
     */
    public function setRulesDir(string $value)
    {
        $this->rulesDir = $value;

        return $this;
    }
    //endregion

    //region Option - formattersDir.
    /**
     * An additional formatters directory, for user-created formatters.
     *
     * @var string
     */
    protected $formattersDir = '';

    public function getFormattersDir(): string
    {
        return $this->formattersDir;
    }

    /**
     * @return $this
     */
    public function setFormattersDir(string $directory)
    {
        $this->formattersDir = $directory;

        return $this;
    }
    //endregion

    //region Option - format.
    /**
     * @var string
     */
    protected $format = '';

    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * The formatter to use to format the results.
     *
     * @return $this
     */
    public function setFormat(string $value)
    {
        $this->format = $value;

        return $this;
    }
    //endregion

    //region Option - project.
    /**
     * @var string
     */
    protected $project = '';

    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * The location of a tsconfig.json file that will be used to determine which files will be linted.
     *
     * @return $this
     */
    public function setProject(string $value)
    {
        $this->project = $value;

        return $this;
    }
    //endregion

    //region Option - typeCheck.
    /**
     * @var bool
     */
    protected $typeCheck = false;

    public function getTypeCheck(): bool
    {
        return $this->typeCheck;
    }

    /**
     * Enables the type checker when running linting rules.
     *
     * The --project must be specified in order to enable type checking.
     *
     * @return $this
     */
    public function setTypeCheck(bool $value)
    {
        $this->typeCheck = $value;

        return $this;
    }
    //endregion

    //region Option - paths.
    /**
     * @var array
     */
    protected $paths = [];

    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * TypeScript files to check.
     *
     * @param string|string[]|bool[] $paths
     *   Key-value pair of file names and boolean.
     * @param bool $include
     *   Exclude or include the files in $paths.
     *
     * @return $this
     */
    public function setPaths(array $paths, bool $include = true)
    {
        $this->paths = $this->createIncludeList($paths, $include) + $this->paths;

        return $this;
    }
    //endregion

    //region Option - lintReporters.
    /**
     * @var string[]|\Sweetchuck\LintReport\ReporterInterface[]
     */
    protected $lintReporters = [];

    /**
     * @return string[]|\Sweetchuck\LintReport\ReporterInterface[]
     */
    public function getLintReporters()
    {
        return $this->lintReporters;
    }

    /**
     * @param array $lintReporters
     *
     * @return $this
     */
    public function setLintReporters(array $lintReporters)
    {
        $this->lintReporters = $lintReporters;

        return $this;
    }

    /**
     * @param string $id
     * @param string|\Sweetchuck\LintReport\ReporterInterface $lintReporter
     *
     * @return $this
     */
    public function addLintReporter($id, $lintReporter = null)
    {
        $this->lintReporters[$id] = $lintReporter;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeLintReporter(string $id)
    {
        unset($this->lintReporters[$id]);

        return $this;
    }
    //endregion
    //endregion

    protected $options = [
        'config' => 'value',
        'exclude' => 'multi-value',
        'force' => 'flag',
        'out' => 'value',
        'rules-dir' => 'value',
        'formatters-dir' => 'value',
        'format' => 'value',
        'project' => 'value',
        'type-check' => 'flag',
    ];

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
        1 => 'Lints with a severity of warning were reported (no errors)',
        2 => 'One or more errors were reported (and any number of warnings)',
        3 => 'Extra lint reporters can be used only if the output format is "json".',
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
        $this->setPaths($paths);
    }

    /**
     * All in one configuration.
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
                    $this->setWorkingDirectory($value);
                    break;

                case 'tslintExecutable':
                    $this->setTslintExecutable($value);
                    break;

                case 'failOn':
                    $this->setFailOn($value);
                    break;

                case 'configFile':
                    $this->setConfigFile($value);
                    break;

                case 'exclude':
                    $this->setExclude($value);
                    break;

                case 'force':
                    $this->setForce($value);
                    break;

                case 'out':
                    $this->setOut($value);
                    break;

                case 'rulesDir':
                    $this->setRulesDir($value);
                    break;

                case 'formattersDir':
                    $this->setFormattersDir($value);
                    break;

                case 'format':
                    $this->setFormat($value);
                    break;

                case 'lintReporters':
                    $this->setLintReporters($value);
                    break;

                case 'project':
                    $this->setProject($value);
                    break;

                case 'typeCheck':
                    $this->setTypeCheck($value);
                    break;

                case 'paths':
                    $this->setPaths($value);
                    break;
            }
        }

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
    protected function createIncludeList($items, $include): array
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
        $lintReporters = $this->initLintReporters();
        if ($lintReporters && $this->getFormat() === '') {
            $this->setFormat('json');
        }

        $command = $this->getCommand();
        $this->printTaskInfo(
            'TsLint task runs: <info>{command}</info> in directory "<info>{workingDirectory}</info>"',
            [
                'command' => $command,
                'workingDirectory' => $this->workingDirectory ?: '.',
            ]
        );

        if ($lintReporters && !$this->isOutputFormatMachineReadable()) {
            $this->exitCode = static::EXIT_CODE_INVALID;

            return new Result($this, $this->exitCode, $this->getExitMessage($this->exitCode));
        }

        /** @var Process $process */
        $process = new $this->processClass($command);

        $result = $this->prepareOutputDirectory();
        if (!$result->wasSuccessful()) {
            return $result;
        }

        $this->exitCode = $process->run();

        $numOfErrors = $this->exitCode;
        $numOfWarnings = 0;
        if ($this->isLintSuccess()) {
            $originalOutput = $process->getOutput();
            if ($this->isOutputFormatMachineReadable()) {
                $machineOutput = ($this->out ? file_get_contents($this->out) : $originalOutput);
                $reportWrapper = $this->decodeOutput($machineOutput);
                $numOfErrors = $reportWrapper->numOfErrors();
                $numOfWarnings = $reportWrapper->numOfWarnings();

                if ($this->isReportHasToBePutBackIntoJar()) {
                    $this->setAssetJarValue('report', $reportWrapper);
                }

                foreach ($lintReporters as $lintReporter) {
                    $lintReporter
                        ->setReportWrapper($reportWrapper)
                        ->generate();
                }
            }

            if (!$lintReporters) {
                $this->output()->write($originalOutput);
            }
        }

        $exitCode = $this->getTaskExitCode($numOfErrors, $numOfWarnings);

        return new Result(
            $this,
            $exitCode,
            $this->getExitMessage($exitCode) ?: $process->getErrorOutput(),
            [
                'time' => $this->getExecutionTime(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand(): string
    {
        if ($this->getWorkingDirectory()) {
            $cmdPattern = 'cd %s && ';
            $cmdArgs = [
                escapeshellarg($this->getWorkingDirectory()),
            ];
        } else {
            $cmdPattern = '';
            $cmdArgs = [];
        }

        $cmdPattern .= '%s';
        $cmdArgs[] = escapeshellcmd($this->getTslintExecutable());

        $options = $this->buildCommandOptions();
        foreach ($this->options as $optionName => $optionType) {
            switch ($optionType) {
                case 'value':
                    if ($options[$optionName]) {
                        $cmdPattern .= " --$optionName %s";
                        $cmdArgs[] = escapeshellarg($options[$optionName]);
                    }
                    break;

                case 'multi-value':
                    $values = array_keys($options[$optionName], true, true);
                    $cmdPattern .= str_repeat(" --$optionName %s", count($values));
                    foreach ($values as $value) {
                        $cmdArgs[] = escapeshellarg($value);
                    }
                    break;

                case 'flag':
                    if ($options[$optionName]) {
                        $cmdPattern .= " --$optionName";
                    }
                    break;
            }
        }

        $paths = array_keys($this->getPaths(), true, true);
        if ($paths) {
            $cmdPattern .= ' --' . str_repeat(' %s', count($paths));
            foreach ($paths as $path) {
                $cmdArgs[] = escapeshellarg($path);
            }
        }

        return vsprintf($cmdPattern, $cmdArgs);
    }

    protected function buildCommandOptions(): array
    {
        return [
            'config' => $this->getConfigFile(),
            'exclude' => $this->getExclude(),
            'force' => $this->getForce(),
            'out' => $this->getOut(),
            'rules-dir' => $this->getRulesDir(),
            'formatters-dir' => $this->getFormattersDir(),
            'format' => $this->getFormat(),
            'project' => $this->getProject(),
            'type-check' => $this->getTypeCheck(),
        ];
    }

    protected function isReportHasToBePutBackIntoJar(): bool
    {
        return (
            $this->hasAssetJar()
            && $this->getAssetJarMap('report')
            && $this->isLintSuccess()
        );
    }

    protected function isOutputFormatMachineReadable(): bool
    {
        return ($this->getFormat() === 'json');
    }

    protected function decodeOutput(string $output): ReportWrapperInterface
    {
        return new ReportWrapper(json_decode($output, true));
    }

    /**
     * @return \Sweetchuck\LintReport\ReporterInterface[]
     */
    protected function initLintReporters(): array
    {
        $lintReporters = [];
        $c = $this->getContainer();
        foreach ($this->getLintReporters() as $id => $lintReporter) {
            if ($lintReporter === false) {
                continue;
            }

            if (!$lintReporter) {
                $lintReporter = $c->get($id);
            } elseif (is_string($lintReporter)) {
                $lintReporter = $c->get($lintReporter);
            }

            if ($lintReporter instanceof ReporterInterface) {
                $lintReporters[$id] = $lintReporter;
                if (!$lintReporter->getDestination()) {
                    $lintReporter
                        ->setFilePathStyle('relative')
                        ->setDestination($this->output());
                }
            }
        }

        return $lintReporters;
    }

    /**
     * Get the exit code regarding the failOn settings.
     */
    protected function getTaskExitCode(int $numOfErrors, int $numOfWarnings): int
    {
        if ($this->isLintSuccess()) {
            switch ($this->getFailOn()) {
                case 'never':
                    return static::EXIT_CODE_OK;

                case 'warning':
                    if ($numOfErrors) {
                        return static::EXIT_CODE_ERROR;
                    }

                    return $numOfWarnings ? static::EXIT_CODE_WARNING : static::EXIT_CODE_OK;

                case 'error':
                    return $numOfErrors ? static::EXIT_CODE_ERROR : static::EXIT_CODE_OK;
            }
        }

        return $this->exitCode;
    }

    protected function getExitMessage(int $exitCode): ?string
    {
        if (isset($this->exitMessages[$exitCode])) {
            return $this->exitMessages[$exitCode];
        }

        return null;
    }

    /**
     * Returns true if the lint ran successfully.
     *
     * Returns true even if there was any code style error or warning.
     */
    protected function isLintSuccess(): bool
    {
        return in_array($this->exitCode, $this->lintSuccessExitCodes());
    }

    /**
     * @return int[]
     */
    protected function lintSuccessExitCodes(): array
    {
        return [
            static::EXIT_CODE_OK,
            static::EXIT_CODE_WARNING,
            static::EXIT_CODE_ERROR,
        ];
    }

    protected function prepareOutputDirectory(): Result
    {
        if (empty($this->out)) {
            return Result::success($this, 'There is no directory to create.');
        }

        $currentDir = getcwd();
        if ($this->workingDirectory) {
            chdir($this->workingDirectory);
        }

        $dir = pathinfo($this->out, PATHINFO_DIRNAME);
        if (!file_exists($dir)) {
            $result = $this->_mkdir($dir);
        } else {
            $result = Result::success($this, 'All directory was created successfully.');
        }

        chdir($currentDir);

        return $result;
    }
}
