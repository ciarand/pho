<?php

use pho\Suite\Suite;
use pho\Runnable\Spec;
use pho\Runnable\Runnable;

describe('Spec', function() {
    $suite = null;

    before(function() use (&$suite) {
        $suite = new Suite('TestSuite', function() {});
    });

    // Only applicable to versions with a Closure::bindTo method
    if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
        it('has its closure bound to the suite', function() use (&$suite) {
            $suite->key = 'testvalue';

            $run = function() use (&$suite) {
                $closure = function() {
                    echo $this->key;
                };
                $spec = new Spec('spec', $closure, $suite);
                $spec->run();
            };

            expect($run)->toPrint('testvalue');
        });
    }

    context('getResult', function() use (&$suite) {
        it('returns PASSED if no exception was thrown', function() use (&$suite) {
            $closure = function() {};
            $spec = new Spec('spec', $closure, $suite);
            $spec->run();

            expect($spec->getResult())->toBe(Spec::PASSED);
        });

        it('returns FAILED if an exception was thrown', function() use (&$suite) {
            $closure = function() {
                throw new \Exception('exception');
            };
            $spec = new Spec('spec', $closure, $suite);
            $spec->run();

            expect($spec->getResult())->toBe(Spec::FAILED);
        });

        it('returns INCOMPLETE if no closure was ran', function() use (&$suite) {
            $spec = new Spec('spec', null, $suite);
            $spec->run();

            expect($spec->getResult())->toBe(Spec::INCOMPLETE);
        });
    });

    context('__toString', function() use (&$suite) {
        it('returns the suite title followed by the spec title', function() use (&$suite) {
            $closure = function() {};
            $spec = new Spec('SpecTitle', $closure, $suite);

            expect((string) $spec)->toEqual('TestSuite SpecTitle');
        });
    });
});
