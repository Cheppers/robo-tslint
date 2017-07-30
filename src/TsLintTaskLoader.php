<?php

namespace Sweetchuck\Robo\TsLint;

use League\Container\ContainerAwareInterface;
use Robo\Contract\OutputAwareInterface;

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
     * @return \Sweetchuck\Robo\TsLint\Task\TsLintRunTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskTsLintRun(array $options = [], array $paths = [])
    {
        /** @var \Sweetchuck\Robo\TSLint\Task\TsLintRunTask $task */
        $task = $this->task(Task\TsLintRunTask::class, $options, $paths);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }
}
