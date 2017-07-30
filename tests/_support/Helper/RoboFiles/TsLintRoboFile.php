<?php

namespace Sweetchuck\Robo\TsLint\Test\Helper\RoboFiles;

use Robo\Tasks;
use Sweetchuck\LintReport\Reporter\BaseReporter;
use Sweetchuck\LintReport\Reporter\SummaryReporter;
use Sweetchuck\LintReport\Reporter\VerboseReporter;
use League\Container\ContainerInterface;
use Sweetchuck\Robo\TsLint\TsLintTaskLoader;

// @codingStandardsIgnoreStart
class TsLintRoboFile extends Tasks
{
    // @codingStandardsIgnoreEnd

    use TsLintTaskLoader;

    /**
     * @var string
     */
    protected $reportsDir = 'actual';

    /**
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        BaseReporter::lintReportConfigureContainer($this->container);

        return $this;
    }

    /**
     * @return \Sweetchuck\Robo\TsLint\Task\TsLintRunTask|\Robo\Collection\CollectionBuilder
     */
    public function lintStylishStdOutput()
    {
        return $this
            ->taskTsLintRun()
            ->setPaths(['samples/*'])
            ->setFormat('stylish');
    }

    /**
     * @return \Sweetchuck\Robo\TsLint\Task\TsLintRunTask|\Robo\Collection\CollectionBuilder
     */
    public function lintStylishFile()
    {
        return $this
            ->taskTsLintRun()
            ->setPaths(['samples/*'])
            ->setFormat('stylish')
            ->setOut("{$this->reportsDir}/native.stylish.txt");
    }

    /**
     * @return \Sweetchuck\Robo\TsLint\Task\TsLintRunTask|\Robo\Collection\CollectionBuilder
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
            ->setPaths(['samples/*'])
            ->setFormat('json')
            ->setFailOn('warning')
            ->addLintReporter('verbose:StdOutput', 'lintVerboseReporter')
            ->addLintReporter('verbose:file', $verboseFile)
            ->addLintReporter('summary:StdOutput', 'lintSummaryReporter')
            ->addLintReporter('summary:file', $summaryFile);
    }
}
