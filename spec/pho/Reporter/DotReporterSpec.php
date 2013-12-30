<?php

use pho\Runnable\Spec;
use pho\Suite\Suite;
use pho\Reporter\DotReporter;
use pho\Reporter\ReporterInterface;
use pho\Console\Console;

describe('DotReporter', function() {
    $console = null;
    $reporter = null;
    $spec = null;

    before(function() use (&$console, &$reporter, &$spec) {
        $console = new Console(array(), 'php://output');
        $console->parseArguments();

        $reporter = new DotReporter($console);

        $suite = new Suite('test', function(){});
        $spec = new Spec('testspec', function(){}, $suite);
    });

    it('implements the ReporterInterface', function() use (&$reporter) {
        expect($reporter instanceof ReporterInterface)->toBeTrue();
    });

    context('beforeSpec', function() use (&$reporter, &$spec) {
        it('increments the spec count', function() use (&$reporter, &$spec) {
            $countBefore = $reporter->getSpecCount();
            $reporter->beforeSpec($spec);
            $countAfter = $reporter->getSpecCount();

            expect($countAfter)->toEqual($countBefore + 1);
        });

        it('prints a newline after a limit', function() use (&$reporter, &$spec) {
            $print = function() use ($reporter, $spec) {
                for ($i = 0; $i <= 60; $i++) {
                    $reporter->beforeSpec($spec);
                    $reporter->afterSpec($spec);
                }
            };

            // TODO: Add pattern matching to toPrint, use '/.*\n/'
            $expected = '...................................................' .
                        '.........' . PHP_EOL . '.';
            expect($print)->toPrint($expected);
        });
    });

    context('afterSpec', function() use (&$console, &$reporter, &$spec) {
        it('prints a dot if the spec passed', function() use (&$reporter, &$spec) {
            $afterSpec = function() use ($reporter, $spec) {
                $reporter->afterSpec($spec);
            };

            expect($afterSpec)->toPrint('.');
        });

        it('prints an F in red if a spec failed', function() use (&$console, &$reporter) {
            $suite = new Suite('test', function(){});
            $spec = new Spec('testspec', function() {
                throw new \Exception('test');
            }, $suite);
            $spec->run();

            $afterSpec = function() use ($spec, $reporter) {
                $reporter->afterSpec($spec);
            };

            expect($afterSpec)->toPrint($console->formatter->red('F'));
        });

        it('prints an I in cyan if incomplete', function() use (&$console, &$reporter) {
            $suite = new Suite('test', function(){});
            $spec = new Spec('testspec', null, $suite);
            $spec->run();

            $afterSpec = function() use ($reporter, $spec) {
                $reporter->afterSpec($spec);
            };

            expect($afterSpec)->toPrint($console->formatter->cyan('I'));
        });
    });
});
