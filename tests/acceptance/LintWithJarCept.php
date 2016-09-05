<?php

/**
 * @var \Codeception\Scenario $scenario
 */

$i = new AcceptanceTester($scenario);
$i->wantTo('tslint --format yaml | tslint-formatters yaml2jsonGroupByFiles');
$i->runRoboTask('lint:with-jar');
$i->theExitCodeShouldBe(1);
$i->seeThisTextInTheStdError('One or more errors were reported (and any number of warnings)');
