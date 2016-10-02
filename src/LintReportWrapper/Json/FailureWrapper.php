<?php

namespace Cheppers\Robo\TsLint\LintReportWrapper\Json;

use Cheppers\LintReport\FailureWrapperInterface;

/**
 * Class FileWrapper.
 *
 * @package Cheppers\LintReport\Wrapper\TSLintJson
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
            'severity' => '',
            'source' => '',
            'message' => '',
            'line' => 0,
            'column' => 0,
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
        return $this->failure['source'];
    }

    /**
     * @return int
     */
    public function line()
    {
        return $this->failure['line'];
    }

    /**
     * @return int
     */
    public function column()
    {
        return $this->failure['column'];
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->failure['message'];
    }
}
