<?php

use \PHPUnit_Framework_Assert as Assert;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * @return $this
     */
    public function clearTheReportsDir()
    {
        $reportsDir = 'tests/_data/reports';
        if (is_dir($reportsDir)) {
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->in($reportsDir);
            foreach ($finder->files() as $file) {
                unlink($file->getPathname());
            }
        }

        return $this;
    }

    /**
     * @param string $taskName
     *
     * @return $this
     */
    public function runRoboTask($taskName)
    {
        $cmd = sprintf(
            'cd tests/_data && ../../bin/robo %s',
            escapeshellarg($taskName)
        );

        $this->runShellCommand($cmd);

        return $this;
    }

    /**
     * @param string $expected
     *
     * @return $this
     */
    public function seeThisTextInTheStdOutput($expected)
    {
        Assert::assertContains($expected, $this->getStdOutput());

        return $this;
    }

    /**
     * @param string $expected
     *
     * @return $this
     */
    public function seeThisTextInTheStdError($expected)
    {
        Assert::assertContains($expected, $this->getStdError());

        return $this;
    }

    /**
     * @param int $expected
     *
     * @return $this
     */
    public function theExitCodeShouldBe($expected)
    {
        Assert::assertEquals($expected, $this->getExitCode());

        return $this;
    }
}
