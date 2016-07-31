<?php
/**
 * @var \Codeception\Scenario $scenario
 */

use \PHPUnit_Framework_Assert as Assert;

$dataDir = rtrim(codecept_data_dir(), '/');

$i = new AcceptanceTester($scenario);
$i->wantTo('tslint --format verbose');
$cmd = sprintf('bin/robo --load-from %s lint:verbose', escapeshellarg($dataDir));
$i->runShellCommand($cmd);
Assert::assertContains(
    '(whitespace) samples/invalid-01.d.ts[5, 16]: missing whitespace',
    $i->getStdOutput()
);
Assert::assertContains(
    '(whitespace) samples/invalid-02.d.ts[5, 16]: missing whitespace',
    $i->getStdOutput()
);
