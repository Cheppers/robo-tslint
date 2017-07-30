<?php

namespace Sweetchuck\Robo\TsLint\LintReportWrapper;

use Sweetchuck\LintReport\ReportWrapperInterface;

class ReportWrapper implements ReportWrapperInterface
{
    /**
     * @var array
     */
    protected $report = [];

    /**
     * @var array
     */
    protected $reportInternal = [];

    /**
     * @var int
     */
    protected $numOfErrors = 0;

    /**
     * @var int
     */
    protected $numOfWarnings = 0;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $report = null)
    {
        if ($report !== null) {
            $this->setReport($report);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReport(): array
    {
        return $this->report;
    }

    /**
     * {@inheritdoc}
     */
    public function setReport(array $report)
    {
        $this->report = $report;
        $this->reportInternal = [];
        $this->numOfErrors = 0;
        $this->numOfWarnings = 0;

        foreach ($report as $failure) {
            $failure += ['severity' => 'error'];
            $filePath = $failure['name'];
            if (!isset($this->reportInternal[$filePath])) {
                $this->reportInternal[$filePath] = [
                    'filePath' => $filePath,
                    'errors' => 0,
                    'warnings' => 0,
                    'stats' => [],
                    'failures' => [],
                ];
            }

            $this->reportInternal[$filePath]['failures'][] = $failure;

            if ($failure['severity'] === 'error') {
                $this->reportInternal[$filePath]['errors']++;
                $this->numOfErrors++;
            } elseif ($failure['severity'] === 'warning') {
                $this->reportInternal[$filePath]['warnings']++;
                $this->numOfWarnings++;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function countFiles(): int
    {
        return count($this->reportInternal);
    }

    /**
     * {@inheritdoc}
     */
    public function yieldFiles()
    {
        foreach ($this->reportInternal as $filePath => $file) {
            yield $filePath => new FileWrapper($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function highestSeverity(): string
    {
        if ($this->numOfErrors()) {
            return ReportWrapperInterface::SEVERITY_ERROR;
        }

        if ($this->numOfWarnings()) {
            return ReportWrapperInterface::SEVERITY_WARNING;
        }

        return ReportWrapperInterface::SEVERITY_OK;
    }

    /**
     * {@inheritdoc}
     */
    public function numOfErrors(): int
    {
        return $this->numOfErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function numOfWarnings(): int
    {
        return $this->numOfWarnings;
    }
}
