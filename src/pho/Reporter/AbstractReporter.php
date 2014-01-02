<?php

namespace pho\Reporter;

use pho\Console\Console;
use pho\Suite\Suite;
use pho\Runnable\Spec;

abstract class AbstractReporter
{
    protected $console;

    protected $formatter;

    protected $startTime;

    protected $specCount;

    protected $failedSpecs;

    protected $incompleteSpecs;

    /**
     * Inherited by Reporter classes to generate console output when pho is
     * ran using the command line.
     *
     * @param Console $console A console for writing output
     */
    public function __construct(Console $console)
    {
        $this->console = $console;
        $this->formatter = $console->formatter;
        $this->startTime = microtime(true);
        $this->specCount = 0;
        $this->failedSpecs = array();
    }

    /**
     * Ran prior to test suite execution.
     */
    public function beforeRun()
    {
        // Do nothing
    }

    /**
     * Returns the number of specs ran by the reporter.
     *
     * @returns int The number of specs ran
     */
    public function getSpecCount()
    {
        return $this->specCount;
    }

    /**
     * Invoked after the test suite has ran, allowing for the display of test
     * results and related statistics.
     */
    public function afterRun()
    {
        if (count($this->failedSpecs)) {
            $this->console->writeLn("\nFailures:");
        }

        foreach ($this->failedSpecs as $spec) {
            $failedText = $this->formatter->red("\n\"$spec\" FAILED");
            $this->console->writeLn($failedText);
            $this->console->writeLn($spec->exception);
        }

        if ($this->startTime) {
            $endTime = microtime(true);
            $runningTime = round($endTime - $this->startTime, 5);
            $this->console->writeLn("\nFinished in $runningTime seconds");
        }

        $failedCount = count($this->failedSpecs);
        $incompleteCount = count($this->incompleteSpecs);
        $specs = ($this->specCount == 1) ? 'spec' : 'specs';
        $failures = ($failedCount == 1) ? 'failure' : 'failures';
        $incomplete = ($incompleteCount) ? ", $incompleteCount incomplete" : '';

        // Print ASCII art if enabled
        if ($this->console->options['ascii']) {
            $this->console->writeLn('');
            $this->drawAscii();
        }

        $summaryText = "\n{$this->specCount} $specs, $failedCount $failures" .
                       $incomplete;

        // Generate the summary based on whether or not it passed
        if ($failedCount) {
            $summary = $this->formatter->red($summaryText);
        } else {
            $summary = $this->formatter->green($summaryText);
        }

        $summary = $this->formatter->bold($summary);
        $this->console->writeLn($summary);
    }

    /**
     * Ran before the containing test suite is invoked.
     *
     * @param Suite $suite The test suite before which to run this method
     */
    public function beforeSuite(Suite $suite)
    {
        return;
    }

    /**
     * Ran after the containing test suite is invoked.
     *
     * @param Suite $suite The test suite after which to run this method
     */
    public function afterSuite(Suite $suite)
    {
        return;
    }

    /**
     * Prints ASCII art based on whether or not any specs failed. If all specs
     * passed, the randomly selected art is of a happier variety, otherwise
     * there's a lot of anger and flipped tables.
     */
    private function drawAscii()
    {
        $fail = array(
            '(╯°□°）╯︵ ┻━┻',
            '¯\_(ツ)_/¯',
            '┻━┻︵ \(°□°)/ ︵ ┻━┻',
            '(ಠ_ಠ)',
            '(ノಠ益ಠ)ノ彡',
            '(✖﹏✖)'
        );

        $pass = array(
            '☜(ﾟヮﾟ☜)',
            '♪ヽ( ⌒o⌒)人(⌒-⌒ )v ♪',
            '┗(^-^)┓',
            'ヽ(^。^)ノ',
            'ヽ(^▽^)v'
        );

        if (count($this->failedSpecs)) {
            $key = array_rand($fail, 1);
            $this->console->writeLn($fail[$key]);
        } else {
            $key = array_rand($pass, 1);
            $this->console->writeLn($pass[$key]);
        }
    }
}
