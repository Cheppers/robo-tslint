<?php
/**
 * @var \Codeception\Scenario $scenario
 */

use \PHPUnit_Framework_Assert as Assert;

$dataDir = rtrim(codecept_data_dir(), '/');
$cmd = sprintf('bin/robo --load-from %s lint:with-jar', escapeshellarg($dataDir));

$i = new AcceptanceTester($scenario);
$i->wantTo('tslint --format yaml | tslint-formatters yaml2jsonGroupByFiles');
$i->runShellCommand($cmd);
Assert::assertEquals(1, $i->getExitCode());
Assert::assertContains(
    'One or more errors were reported (and any number of warnings)',
    $i->getStdError()
);
