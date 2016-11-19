<?php

namespace Cheppers\Robo\TsLint\Task;

use Cheppers\AssetJar\AssetJarAware;
use Cheppers\AssetJar\AssetJarAwareInterface;
use Cheppers\LintReport\ReporterInterface;
use Cheppers\LintReport\ReportWrapperInterface;
use Cheppers\Robo\TsLint\LintReportWrapper\Json\ReportWrapper as JsonReportWrapper;
use Cheppers\Robo\TsLint\LintReportWrapper\Yaml\ReportWrapper as YamlReportWrapper;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use PackageVersions\Versions;
use Robo\Common\BuilderAwareTrait;
use Robo\Common\IO;
use Robo\Common\OutputAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\OutputAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Task\Filesystem\loadTasks as FsLoadTasks;
use Robo\Task\Filesystem\loadShortcuts as FsShortCuts;
use Robo\TaskAccessor;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TaskTsLintRun.
 *
 * Assert mapping:
 *   - report: Parsed JSON lint report.
 *
 * @package Cheppers\Robo\TsLint\Task
 */
class Run extends BaseTask implements
    AssetJarAwareInterface,
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
     * @var \Cheppers\LintReport\ReporterInterface[]
     */
    protected $lintReporters = [];

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

                case 'lintReporters':
                    $this->setLintReporters($value);
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
     * @return \Cheppers\LintReport\ReporterInterface[]
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
     * @param string|\Cheppers\LintReport\ReporterInterface $lintReporter
     *
     * @return $this
     */
    public function addLintReporter($id, $lintReporter = null)
    {
        $this->lintReporters[$id] = $lintReporter;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function removeLintReporter($id)
    {
        unset($this->lintReporters[$id]);

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

        $lintReporters = $this->initLintReporters();
        if ($lintReporters && !$this->isOutputFormatMachineReadable()) {
            $this->exitCode = static::EXIT_CODE_INVALID;

            return new Result($this, $this->exitCode, $this->getExitMessage($this->exitCode));
        }

        /** @var Process $process */
        $process = new $this->processClass($command);
        if ($this->workingDirectory) {
            $process->setWorkingDirectory($this->workingDirectory);
        }

        $result = $this->prepareOutputDirectory();
        if (!$result->wasSuccessful()) {
            return $result;
        }

        $this->startTimer();
        $this->exitCode = $process->run();
        $this->stopTimer();

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
     * @return bool
     */
    protected function isReportHasToBePutBackIntoJar()
    {
        return (
            $this->hasAssetJar()
            && $this->getAssetJarMap('report')
            && $this->isLintSuccess()
        );
    }

    /**
     * @return bool
     */
    protected function isOutputFormatMachineReadable()
    {
        return ($this->format === 'yaml');
    }

    /**
     * @param string $output
     *
     * @return ReportWrapperInterface
     */
    protected function decodeOutput($output)
    {
        $format = ($this->convertFormatTo === 'yaml2jsonGroupByFiles' ? 'json' : 'yaml');

        switch ($format) {
            case 'json':
                return new JsonReportWrapper(json_decode($output, true));

            case 'yaml':
                if (function_exists('yaml_parse')) {
                    $decoded = yaml_parse($output);
                } else {
                    $yamlVersion = ltrim(Versions::getShortVersion('symfony/yaml'), 'v');
                    if (version_compare($yamlVersion, '3.1.0', '>=')) {
                        $decoded = Yaml::parse($output, Yaml::PARSE_OBJECT_FOR_MAP);
                    } else {
                        $decoded = Yaml::parse($output);
                    }
                }

                return new YamlReportWrapper($decoded);
        }

        return null;
    }

    /**
     * @return \Cheppers\LintReport\ReporterInterface[]
     */
    protected function initLintReporters()
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
     *
     * @param int $numOfErrors
     * @param int $numOfWarnings
     *
     * @return int
     */
    protected function getTaskExitCode($numOfErrors, $numOfWarnings)
    {
        if ($this->isLintSuccess()) {
            switch ($this->failOn) {
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

    /**
     * @param int $exitCode
     *
     * @return string
     */
    protected function getExitMessage($exitCode)
    {
        if (isset($this->exitMessages[$exitCode])) {
            return $this->exitMessages[$exitCode];
        }

        return false;
    }

    /**
     * Returns true if the lint ran successfully.
     *
     * Returns true even if there was any code style error or warning.
     *
     * @return bool
     */
    protected function isLintSuccess()
    {
        return in_array($this->exitCode, $this->lintSuccessExitCodes());
    }

    /**
     * @return int[]
     */
    protected function lintSuccessExitCodes()
    {
        return [
            static::EXIT_CODE_OK,
            static::EXIT_CODE_WARNING,
            static::EXIT_CODE_ERROR,
        ];
    }

    /**
     * Prepare directory for report outputs.
     *
     * @return null|\Robo\Result
     *   Returns NULL on success or an error \Robo\Result.
     */
    protected function prepareOutputDirectory()
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
