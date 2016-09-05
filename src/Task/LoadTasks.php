<?php

namespace Cheppers\Robo\TsLint\Task;

/**
 * Class LoadTasks.
 *
 * @package Cheppers\Robo\TsLint\Task
 */
trait LoadTasks
{
    /**
     * Wrapper for tslint.
     *
     * @param array $options
     *   Key-value pairs of options.
     * @param string[] $paths
     *   File paths.
     *
     * @return \Cheppers\Robo\TsLint\Task\Run
     *   A lint runner task instance.
     */
    protected function taskTsLintRun(array $options = [], array $paths = [])
    {
        return $this->task(Run::class, $options, $paths);
    }
}
