<?php

// @codingStandardsIgnoreStart

/**
 * Class RoboFile.
 */
class RoboFile extends \Robo\Tasks
{
    // @codingStandardsIgnoreEnd

    use \Cheppers\Robo\TsLint\Task\LoadTasks;

    /**
     * @return \Cheppers\Robo\TsLint\Task\Run
     */
    public function lintVerbose()
    {
        return $this
            ->taskTsLintRun()
            ->setOutput($this->getOutput())
            ->format('verbose')
            ->paths(['samples/*']);
    }

    /**
     * @return \Cheppers\Robo\TsLint\Task\Run
     */
    public function lintWithJar()
    {
        $assetJar = new \Cheppers\AssetJar\AssetJar([]);

        return $this
            ->taskTsLintRun()
            ->setOutput($this->getOutput())
            ->setAssetJar($assetJar)
            ->setAssetJarMap('report', ['taskTsLint', 'report'])
            ->configFile('tslint.json')
            ->formattersDir('node_modules/tslint-formatters/lib/tslint/formatters')
            ->format('yaml')
            ->convertFormatTo('yaml2jsonGroupByFiles')
            ->paths(['samples/*']);
    }

}
