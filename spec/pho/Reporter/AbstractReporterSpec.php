<?php

namespace spec;

use pho\Runnable\Spec;
use pho\Suite\Suite;
use pho\Reporter\AbstractReporter;
use pho\Reporter\ReporterInterface;
use pho\Console\Console;

// Require a mock reporter
class MockReporter extends AbstractReporter implements ReporterInterface
{
    public function beforeSpec(Spec $spec)
    {
        $this->specCount += 1;
    }

    public function afterSpec(Spec $spec)
    {
        return;
    }
}

describe('AbstractReporter', function() {
    $console = new Console(array(), 'php://output');
    $console->parseArguments();

    $reporter = new MockReporter($console);
    $printContents = null;

    context('afterRun', function() use (&$reporter, &$printContents) {
        before(function() use (&$reporter, &$printContents) {
            // Add a spec and run corresponding reporter hooks
            $suite = new Suite('test', function(){});
            $spec = new Spec('testspec', function(){}, $suite);
            $reporter->beforeSpec($spec);
            $reporter->afterSpec($spec);

            ob_start();
            $reporter->afterRun();
            $printContents = ob_get_contents();
            ob_end_clean();
        });

        it('prints the running time', function() use (&$printContents) {
            // TODO: Update once pattern matching is added
            $print = $printContents;
            expect($print)->toContain('Finished in');
            expect($print)->toContain('seconds');
        });

        it('prints the number of specs and failures', function() use (&$printContents) {
            expect($printContents)->toContain('1 spec, 0 failures');
        });
    });
});
