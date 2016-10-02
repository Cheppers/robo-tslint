<?php

use Cheppers\LintReport\Reporter\BaseReporter;
use Cheppers\LintReport\Reporter\SummaryReporter;
use Cheppers\LintReport\Reporter\VerboseReporter;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerInterface;
use Robo\Contract\ConfigAwareInterface;

/**
 * Class RoboFile.
 */
// @codingStandardsIgnoreStart
class RoboFile extends \Robo\Tasks implements ContainerAwareInterface, ConfigAwareInterface
{
    // @codingStandardsIgnoreEnd
    use \Cheppers\Robo\TsLint\Task\LoadTasks;
    use \League\Container\ContainerAwareTrait;
    use \Robo\Common\ConfigAwareTrait;

    /**
     * @var string
     */
    protected $reportsDir = 'actual';

    /**
     * @param \League\Container\ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        BaseReporter::lintReportConfigureContainer($this->container);

        return $this;
    }

    /**
     * @return \Cheppers\Robo\TsLint\Task\Run
     */
    public function lintStylishStdOutput()
    {
        return $this
            ->taskTsLintRun()
            ->paths(['samples/*'])
            ->format('stylish');
    }

    /**
     * @return \Cheppers\Robo\TsLint\Task\Run
     */
    public function lintStylishFile()
    {
        return $this
            ->taskTsLintRun()
            ->paths(['samples/*'])
            ->format('stylish')
            ->out("{$this->reportsDir}/native.stylish.txt");
    }

    /**
     * @return \Cheppers\Robo\TsLint\Task\Run
     */
    public function lintAllInOne()
    {
        $verboseFile = new VerboseReporter();
        $verboseFile
            ->setFilePathStyle('relative')
            ->setDestination("{$this->reportsDir}/extra.verbose.txt");

        $summaryFile = new SummaryReporter();
        $summaryFile
            ->setFilePathStyle('relative')
            ->setDestination("{$this->reportsDir}/extra.summary.txt");

        return $this->taskTsLintRun()
            ->paths(['samples/*'])
            ->format('yaml')
            ->convertFormatTo('yaml2jsonGroupByFiles')
            ->failOn('warning')
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile);
    }
}
