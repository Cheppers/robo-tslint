<?php

/**
 * @var \Codeception\Scenario $scenario
 */

$i = new \AcceptanceTester($scenario);
$i->wantTo('tslint --format verbose');

$i->runRoboTask('lint:verbose');
$i->theExitCodeShouldBe(2);
$i->seeThisTextInTheStdOutput('(whitespace) samples/invalid-01.d.ts[5, 16]: missing whitespace');
$i->seeThisTextInTheStdOutput('(whitespace) samples/invalid-02.d.ts[5, 16]: missing whitespace');
