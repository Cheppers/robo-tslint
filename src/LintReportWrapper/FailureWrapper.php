<?php

namespace Sweetchuck\Robo\TsLint\LintReportWrapper;

use Sweetchuck\LintReport\FailureWrapperInterface;
use Sweetchuck\LintReport\ReportWrapperInterface;

class FailureWrapper implements FailureWrapperInterface
{
    /**
     * @var array
     */
    protected $failure = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $failure)
    {
        // @todo Validate.
        $this->failure = $failure + [
            'severity' => ReportWrapperInterface::SEVERITY_OK,
            'failure' => '',
            'name' => '',
            'ruleName' => '',
            'startPosition' => [
                'character' => 0,
                'line' => 0,
                'position' => 0,
            ],
            'endPosition' => [
                'character' => 0,
                'line' => 0,
                'position' => 0,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function severity(): string
    {
        return $this->failure['severity'];
    }

    /**
     * {@inheritdoc}
     */
    public function source(): string
    {
        return $this->failure['ruleName'];
    }

    /**
     * {@inheritdoc}
     */
    public function line(): int
    {
        return $this->failure['startPosition']['line'];
    }

    /**
     * {@inheritdoc}
     */
    public function column(): int
    {
        return $this->failure['startPosition']['character'];
    }

    /**
     * {@inheritdoc}
     */
    public function message(): string
    {
        return $this->failure['failure'];
    }
}
