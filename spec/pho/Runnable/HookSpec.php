<?php

use pho\Suite\Suite;
use pho\Runnable\Hook;
use pho\Runnable\Runnable;

describe('Hook', function() {
    // Only applicable to versions with a Closure::bindTo method
    if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
        before(function() {
            $this->suite = new Suite('TestSuite', function() {});
        });

        it('has its closure bound to the suite', function() {
            $suite = $this->suite;
            $suite->key = 'testvalue';

            $run = function() {
                $closure = function() {
                    echo $this->key;
                };
                $hook = new Hook($closure, $this->suite);
                $hook->run();
            };

            expect($run)->toPrint('testvalue');
        });
    }
});
