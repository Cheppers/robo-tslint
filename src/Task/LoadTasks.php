<?php

namespace Cheppers\Robo\TsLint\Task;

use Robo\Container\SimpleServiceProvider;

/**
 * Class LoadTasks.
 *
 * @package Cheppers\Robo\TsLint\Task
 */
trait LoadTasks
{

    /**
     * @return \League\Container\ServiceProvider\SignatureServiceProviderInterface
     */
    public static function getTsLintServiceProvider()
    {
        return new SimpleServiceProvider([
            'taskTsLintRun' => TaskTsLintRun::class,
        ]);
    }

    /**
     * Wrapper for tslint.
     *
     * @param array $options
     *   Key-value pairs of options.
     * @param string[] $paths
     *   File paths.
     *
     * @return \Cheppers\Robo\TsLint\Task\TaskTsLintRun
     *   A lint runner task instance.
     */
    protected function taskTsLintRun(array $options = [], array $paths = [])
    {
        return $this->task(__FUNCTION__, $options, $paths);
    }
}
