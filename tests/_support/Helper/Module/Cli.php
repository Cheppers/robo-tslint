<?php

namespace Helper\Module;

use Codeception\Module as CodeceptionModule;
use Symfony\Component\Process\Process;

/**
 * Wrapper for basic shell commands and shell output.
 */
class Cli extends CodeceptionModule
{
    /**
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * @var int|null
     */
    protected $exitCode = null;

    /**
     * @var string
     */
    protected $stdOutput = null;

    /**
     * @var string
     */
    protected $stdError = null;

    public function _cleanup()
    {
        $this->process = null;
    }

    /**
     * Executes a shell command.
     *
     * @param string $command
     *
     * @return $this
     */
    public function runShellCommand($command)
    {
        $this->process = new Process($command);
        $this->process->run();

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExitCode()
    {
        if ($this->exitCode === null) {
            $this->exitCode = $this->process->getExitCode();
        }

        return $this->exitCode;
    }

    /**
     * @return string
     */
    public function getStdOutput()
    {
        if ($this->stdOutput === null) {
            $this->stdOutput = $this->process->getOutput();
        }

        return $this->stdOutput;
    }

    /**
     * @return string
     */
    public function getStdError()
    {
        if ($this->stdError === null) {
            $this->stdError = $this->process->getErrorOutput();
        }

        return $this->stdError;
    }
}
