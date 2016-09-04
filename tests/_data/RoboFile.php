<?php

/**
 * Class RoboFile.
 */
class RoboFile extends \Robo\Tasks
    // @codingStandardsIgnoreEnd
{
    use \Cheppers\Robo\TsLint\Task\LoadTasks;

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        $this->setContainer(\Robo\Robo::getContainer());

        /** @var \League\Container\Container $c */
        $c = $this->getContainer();
        $c
            ->addServiceProvider(static::getTsLintServiceProvider())
            ->addServiceProvider(\Robo\Task\Filesystem\loadTasks::getFilesystemServices());
    }

    /**
     * @return \Cheppers\Robo\TsLint\Task\TaskTsLintRun
     */
    public function lintVerbose()
    {
        return $this
            ->taskTsLintRun()
            ->format('verbose')
            ->paths(['samples/*']);
    }

    /**
     * @return \Cheppers\Robo\TsLint\Task\TaskTsLintRun
     */
    public function lintWithJar()
    {
        $asset_jar = new \Cheppers\AssetJar\AssetJar([]);

        return $this
            ->taskTsLintRun()
            ->setAssetJar($asset_jar)
            ->setAssetJarMap('report', ['taskTsLint', 'report'])
            ->configFile('tslint.json')
            ->formattersDir('node_modules/tslint-formatters/lib/tslint/formatters')
            ->format('yaml')
            ->convertFormatTo('yaml2jsonGroupByFiles')
            ->paths(['samples/*']);
    }

}
