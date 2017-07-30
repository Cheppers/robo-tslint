<?php

namespace Sweetchuck\Robo\TsLint\Tests\Acceptance;

use AcceptanceTester;

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

    public function lintAllInOne(AcceptanceTester $I): void
    {
        $I->runRoboTask('lint:all-in-one');
        $I->expectTheExitCodeToBe(2);
        $I->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/extra.verbose.txt"));
        $I->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/extra.summary.txt"));
        $I->haveAFileLikeThis('extra.verbose.txt');
        $I->haveAFileLikeThis('extra.summary.txt');
        $I->seeThisTextInTheStdError('One or more errors were reported (and any number of warnings)');
    }

    public function lintStylishFile(AcceptanceTester $I): void
    {
        $I->runRoboTask('lint:stylish-file');
        $I->expectTheExitCodeToBe(2);
        $I->haveAFileLikeThis('native.stylish.txt');
        $I->seeThisTextInTheStdError('One or more errors were reported (and any number of warnings)');
    }

    public function lintStylishStdOutput(AcceptanceTester $I): void
    {
        $I->runRoboTask('lint:stylish-std-output');
        $I->expectTheExitCodeToBe(2);
        $I->seeThisTextInTheStdOutput(file_get_contents("{$this->expectedDir}/native.stylish.txt"));
        $I->seeThisTextInTheStdError('One or more errors were reported (and any number of warnings)');
    }
}
