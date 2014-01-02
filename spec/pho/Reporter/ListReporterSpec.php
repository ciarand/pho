<?php

use pho\Runnable\Spec;
use pho\Suite\Suite;
use pho\Reporter\ListReporter;
use pho\Reporter\ReporterInterface;
use pho\Console\Console;

describe('ListReporter', function() {
    $console = null;
    $spec = null;

    before(function() use (&$console, &$spec) {
        $console = new Console(array(), 'php://output');
        $console->parseArguments();

        $suite = new Suite('test', function(){});
        $spec = new Spec('testspec', function(){}, $suite);
    });

    it('implements the ReporterInterface', function() use (&$console) {
        $reporter = new ListReporter($console);
        expect($reporter instanceof ReporterInterface)->toBeTrue();
    });

    context('beforeSpec', function() use (&$console, &$spec) {
        it('increments the spec count', function() use (&$console, &$spec) {
            $reporter = new ListReporter($console);

            $countBefore = $reporter->getSpecCount();
            $reporter->beforeSpec($spec);
            $countAfter = $reporter->getSpecCount();

            expect($countAfter)->toEqual($countBefore + 1);
        });
    });

    context('afterSpec', function() use (&$console, &$spec) {
        it('prints the full spec string in grey if it passed', function() use (&$console, &$spec) {
            $reporter = new ListReporter($console);
            $afterSpec = function() use ($reporter, &$spec) {
                $reporter->afterSpec($spec);
            };

            $title = $console->formatter->grey($spec);
            expect($afterSpec)->toPrint($title . PHP_EOL);
        });

        it('prints the full spec string in red if it failed', function() use (&$console, $spec) {
            $suite = new Suite('test', function(){});
            $spec = new Spec('testspec', function() {
                throw new \Exception('test');
            }, $suite);
            $spec->run();

            $afterSpec = function() use (&$console, $spec) {
                $reporter = new ListReporter($console);
                $reporter->afterSpec($spec);
            };

            $specTitle = $console->formatter->red($spec);
            expect($afterSpec)->toPrint($specTitle . PHP_EOL);
        });

        it('prints the full spec string in cyan if incomplete', function() use (&$console, $spec) {
            $suite = new Suite('test', function(){});
            $spec = new Spec('testspec', null, $suite);
            $spec->run();

            $afterSpec = function() use ($console, $spec) {
                $reporter = new ListReporter($console);
                $reporter->afterSpec($spec);
            };

            $specTitle = $console->formatter->cyan($spec);
            expect($afterSpec)->toPrint($specTitle . PHP_EOL);
        });
    });
});
