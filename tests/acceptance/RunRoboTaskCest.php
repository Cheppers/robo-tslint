<?php

namespace Sweetchuck\Robo\TsLint\Tests\Acceptance;

use Sweetchuck\Robo\TsLint\Test\AcceptanceTester;
use Sweetchuck\Robo\TsLint\Test\Helper\RoboFiles\TsLintRoboFile;

class RunRoboTaskCest
{
    /**
     * @var string
     */
    protected $expectedDir = '';

    public function __construct()
    {
        $this->expectedDir = codecept_data_dir('expected');
    }

    // @codingStandardsIgnoreStart
    public function _before(AcceptanceTester $I)
    {
        // @codingStandardsIgnoreEnd
        $I->clearTheReportsDir();
    }

    public function lintAllInOne(AcceptanceTester $i): void
    {
        $id = 'lint:all-in-one';

        $cwd = getcwd();
        chdir(codecept_data_dir());
        $i->runRoboTask(
            $id,
            TsLintRoboFile::class,
            'lint:all-in-one'
        );
        chdir($cwd);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $i->assertEquals(2, $exitCode);

        $i->assertContains(
            file_get_contents("{$this->expectedDir}/extra.verbose.txt"),
            $stdOutput
        );
        $i->assertContains(
            file_get_contents("{$this->expectedDir}/extra.summary.txt"),
            $stdOutput
        );

        $i->assertContains(
            'One or more errors were reported (and any number of warnings)',
            $stdError
        );

        $i->haveAFileLikeThis('extra.verbose.txt');
        $i->haveAFileLikeThis('extra.summary.txt');
    }

    public function lintStylishFile(AcceptanceTester $i): void
    {
        $id = 'lint:stylish-file';

        $cwd = getcwd();
        chdir(codecept_data_dir());
        $i->runRoboTask(
            $id,
            TsLintRoboFile::class,
            'lint:stylish-file'
        );
        chdir($cwd);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdError = $i->getRoboTaskStdError($id);

        $i->assertEquals(2, $exitCode);
        $i->assertContains(
            'One or more errors were reported (and any number of warnings)',
            $stdError
        );
        $i->haveAFileLikeThis('native.stylish.txt');
    }

    public function lintStylishStdOutput(AcceptanceTester $i): void
    {
        $id = 'lint:stylish-std-output';

        $cwd = getcwd();
        chdir(codecept_data_dir());
        $i->runRoboTask(
            $id,
            TsLintRoboFile::class,
            'lint:stylish-std-output'
        );
        chdir($cwd);

        $exitCode = $i->getRoboTaskExitCode($id);
        $stdOutput = $i->getRoboTaskStdOutput($id);
        $stdError = $i->getRoboTaskStdError($id);

        $i->assertEquals(2, $exitCode);
        $i->assertContains(
            file_get_contents("{$this->expectedDir}/native.stylish.txt"),
            $stdOutput
        );
        $i->assertContains(
            'One or more errors were reported (and any number of warnings)',
            $stdError
        );
    }
}
