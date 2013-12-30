<?php

namespace spec;

use pho\Suite\Suite;
use pho\Runnable\Spec;
use pho\Runnable\Runnable;
use pho\Exception\ErrorException;
use pho\Exception\ExpectationException;

class MockRunnable extends Runnable
{
    public function __construct(\Closure $closure, Suite $suite)
    {
        $this->suite = $suite;
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $this->closure = $closure->bindTo($suite);
        } else {
            $this->closure = $closure;
        }
    }
}

describe('Runnable', function() {
    $suite = null;

    before(function() use (&$suite) {
        $suite = new Suite('TestSuite', function() {});
    });

    context('run', function() use (&$suite) {
        it('catches and stores errors', function() use (&$suite) {
            $closure = function() {
                trigger_error('TestError', E_USER_NOTICE);
            };
            $runnable = new MockRunnable($closure, $suite);
            $runnable->run();

            expect($runnable->exception->getType())->toEqual('E_USER_NOTICE');
        });

        it('catches and stores ExpectationExceptions', function() use (&$suite) {
            $closure = function() {
                throw new ExpectationException('test');
            };
            $runnable = new MockRunnable($closure, $suite);
            $runnable->run();

            expect($runnable->exception->getMessage())->toEqual('test');
        });

        it('catches and stores all other exceptions', function() use (&$suite) {
            $closure = function() {
                throw new \Exception('test exception');
            };
            $runnable = new MockRunnable($closure, $suite);
            $runnable->run();

            expect($runnable->exception->getMessage())->toEqual('test exception');
        });
    });
});
