<?php

use pho\Console\Console;

describe('Console', function() {
    context('parseArguments', function() {
        it('parses the arguments with the available options', function() {
            $console = new Console(array('--reporter', 'dot', '-s'), 'php://output');
            $console->parseArguments();

            expect($console->options)->toEqual(array(
                'ascii'     => false,
                'help'      => false,
                'filter'    => false,
                'reporter'  => 'dot',
                'stop'      => true,
                'version'   => false,
                'watch'     => false,
                'namespace' => false
            ));
        });

        context('when the help flag is used', function() {
            $printContents = null;
            $console = null;

            before(function() use (&$printContents, &$console) {
                $console = new Console(array('--help'), 'php://output');

                ob_start();
                $console->parseArguments();
                $printContents = ob_get_contents();
                ob_end_clean();
            });

            it('sets the error status to 0', function() use (&$console) {
                expect($console->getErrorStatus())->toEqual(0);
            });

            it('prints the option list and help', function() use (&$printContents) {
                expect($printContents)
                    ->toContain('Usage: pho [options] [files]')
                    ->toContain('Options')
                    ->toContain('help');
            });
        });

        context('when the version flag is used', function() {
            $printContents = null;
            $console = null;

            before(function() use (&$printContents, &$console) {
                $console = new Console(array('--version'), 'php://output');

                ob_start();
                $console->parseArguments();
                $printContents = ob_get_contents();
                ob_end_clean();
            });

            it('sets the error status to 0', function() use (&$console) {
                expect($console->getErrorStatus())->toEqual(0);
            });

            it('prints version info', function() use (&$printContents) {
                expect($printContents)
                    ->toMatch('/pho version \d.\d.\d/');
            });
        });

        context('when an invalid option is passed', function() {
            $printContents = null;
            $console = null;

            before(function() use (&$printContents, &$console) {
                $console = new Console(array('--invalid'), 'php://output');

                ob_start();
                $console->parseArguments();
                $printContents = ob_get_contents();
                ob_end_clean();
            });

            it('sets the error status to 1', function() use (&$console) {
                expect($console->getErrorStatus())->toEqual(1);
            });

            it('lists the invalid option', function() use (&$printContents) {
                expect($printContents)
                    ->toEqual('--invalid is not a valid option' . PHP_EOL);
            });
        });

        context('when an invalid path is used', function() {
            $printContents = null;
            $console = null;

            before(function() use (&$printContents, &$console) {
                $console = new Console(array('./someinvalidpath'), 'php://output');

                ob_start();
                $console->parseArguments();
                $printContents = ob_get_contents();
                ob_end_clean();
            });

            it('sets the error status to 1', function() use (&$console) {
                expect($console->getErrorStatus())->toEqual(1);
            });

            it('lists the invalid path', function() use (&$printContents) {
                expect($printContents)->toEqual(
                    "The file or path \"./someinvalidpath\" doesn't exist" . PHP_EOL);
            });
        });
    });

    context('getPaths', function() {
        it('returns the array of parsed paths', function() {
            $console = new Console(array('./'), 'php://output');
            $console->parseArguments();

            expect($console->getPaths())->toEqual(array('./'));
        });
    });

    context('getReporterClass', function() {
        it('returns DotReporter by default', function() {
            $console = new Console(array(), 'php://output');
            $console->parseArguments();

            $expectedClass = 'pho\Reporter\DotReporter';
            expect($console->getReporterClass())->toEqual($expectedClass);
        });

        it('returns a valid reporter specified in the args', function() {
            $console = new Console(array('-r', 'spec'), 'php://output');
            $console->parseArguments();

            $expectedClass = 'pho\Reporter\SpecReporter';
            expect($console->getReporterClass())->toEqual($expectedClass);
        });
    });

    context('write', function() {
        it('prints the text to the terminal', function() {
            $write = function() {
                $console = new Console(array(), 'php://output');
                $console->write('test');
            };
            expect($write)->toPrint('test');
        });
    });

    context('writeLn', function() {
        it('prints the text, followed by a newline, to the terminal', function() {
            $writeLn = function() {
                $console = new Console(array(), 'php://output');
                $console->writeLn('test');
            };
            expect($writeLn)->toPrint('test' . PHP_EOL);
        });
    });
});
