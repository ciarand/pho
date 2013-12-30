<?php

namespace pho\Runnable;

use pho\Suite\Suite;

class Hook extends Runnable
{
    /**
     * Constructs a hook object, to be associated with any of a suite's hooks,
     * ie: before, after, beforeEach, and afterEach. The closure is also bound
     * to the suite.
     *
     * @param \Closure $closure The closure to invoke when the hook is called
     * @param Suite    $suite   The suite within which this spec was defined
     */
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
