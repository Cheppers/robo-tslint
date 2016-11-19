<?php

namespace Cheppers\Robo\TsLint;

use League\Container\ContainerAwareInterface;
use Robo\Contract\OutputAwareInterface;

/**
 * Class LoadTasks.
 *
 * @package Cheppers\Robo\TsLint\Task
 */
trait TsLintTaskLoader
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
        /** @var \Cheppers\Robo\TSLint\Task\Run $task */
        $task = $this->task(Task\Run::class, $options, $paths);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }
}
