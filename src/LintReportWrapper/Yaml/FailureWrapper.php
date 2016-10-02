<?php

namespace Cheppers\Robo\TsLint\LintReportWrapper\Yaml;

use Cheppers\LintReport\FailureWrapperInterface;

/**
 * Class FileWrapper.
 *
 * @package Cheppers\LintReport\Wrapper\TSLintYaml
 */
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
            'failure' => '',
            'severity' => 'error',
            'name' => '',
            'ruleName' => '',
            'startPosition' => [
                'line' => 0,
                'character' => 0,
                'position' => 0,
            ],
            'endPosition' => [
                'line' => 0,
                'character' => 0,
                'position' => 0,
            ],
        ];
    }

    /**
     * @return string
     */
    public function severity()
    {
        return $this->failure['severity'];
    }

    /**
     * @return string
     */
    public function source()
    {
        return $this->failure['ruleName'];
    }

    /**
     * @return int
     */
    public function line()
    {
        return $this->failure['startPosition']['line'];
    }

    /**
     * @return int
     */
    public function column()
    {
        return $this->failure['startPosition']['character'];
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->failure['failure'];
    }
}
