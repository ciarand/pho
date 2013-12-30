<?php

use pho\Runnable\Spec;
use pho\Suite\Suite;
use pho\Reporter\SpecReporter;
use pho\Reporter\ReporterInterface;
use pho\Console\Console;

describe('SpecReporter', function() {
    $console = null;
    $spec = null;

    before(function() use (&$console, &$spec) {
        $console = new Console(array(), 'php://output');
        $console->parseArguments();

        $suite = new Suite('test', function(){});
        $spec = new Spec('testspec', function(){}, $suite);
    });

    it('implements the ReporterInterface', function() use (&$console) {
        $reporter = new SpecReporter($console);
        expect($reporter instanceof ReporterInterface)->toBeTrue();
    });

    context('beforeSuite', function() use (&$console) {
        $reporter = null;

        before(function() use (&$console, &$reporter) {
            $reporter = new SpecReporter($console);
        });

        it('prints the suite title', function() use (&$reporter) {
            $beforeSuite = function() use (&$reporter) {
                $suite = new Suite('test suite', function() {});
                $reporter->beforeSuite($suite);
            };

            expect($beforeSuite)->toPrint(PHP_EOL . "test suite" . PHP_EOL);
        });

        it('pads nested suites', function() use (&$reporter) {
            $beforeSuite = function() use (&$reporter) {
                $suite = new Suite('test suite', function() {});
                $reporter->beforeSuite($suite);
            };

            expect($beforeSuite)->toPrint("    test suite" . PHP_EOL);
        });
    });

    context('beforeSpec', function() use (&$console, &$spec) {
        it('increments the spec count', function() use (&$console, &$spec) {
            $reporter = new SpecReporter($console);

            $countBefore = $reporter->getSpecCount();
            $reporter->beforeSpec($spec);
            $countAfter = $reporter->getSpecCount();

            expect($countAfter)->toEqual($countBefore + 1);
        });
    });

    context('afterSpec', function() use (&$console, &$spec) {
        it('prints the spec title in grey if it passed', function() use (&$console, &$spec) {
            $reporter = new SpecReporter($console);
            $afterSpec = function() use ($reporter, $spec) {
                $reporter->afterSpec($spec);
            };

            $title = $console->formatter->grey($spec->getTitle());
            expect($afterSpec)->toPrint($title . PHP_EOL);
        });

        it('prints the spec title in red if it failed', function() use (&$console, &$spec) {
            $suite = new Suite('test', function(){});
            $spec = new Spec('testspec', function() {
                throw new \Exception('test');
            }, $suite);
            $spec->run();

            $afterSpec = function() use ($console, $spec) {
                $reporter = new SpecReporter($console);
                $reporter->afterSpec($spec);
            };

            $specTitle = $console->formatter->red($spec->getTitle());
            expect($afterSpec)->toPrint($specTitle . PHP_EOL);
        });

        it('prints the spec title in cyan if incomplete', function() use (&$console, $spec) {
            $suite = new Suite('test', function(){});
            $spec = new Spec('testspec', null, $suite);
            $spec->run();

            $afterSpec = function() use ($console, $spec) {
                $reporter = new SpecReporter($console);
                $reporter->afterSpec($spec);
            };

            $specTitle = $console->formatter->cyan($spec->getTitle());
            expect($afterSpec)->toPrint($specTitle . PHP_EOL);
        });
    });
});
