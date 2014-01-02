<?php

use pho\Expectation\Matcher\InclusionMatcher;
use pho\Expectation\Matcher\MatcherInterface;

describe('InclusionMatcher', function() {
    it('implements the MatcherInterface', function() {
        $matcher = new InclusionMatcher('test');

        if (!($matcher instanceof MatcherInterface)) {
            throw new \Exception('Does not implement MatcherInterface');
        }
    });

    context('match with a single constructor argument', function() {
        it('returns true if the substring is found in the string', function() {
            $matcher = new InclusionMatcher(array('String'));
            if (!$matcher->match('TestString')) {
                throw new \Exception('Does not return true');
            }
        });

        it('returns false if the substring is not found in the string', function() {
            $matcher = new InclusionMatcher(array('String'));
            if ($matcher->match('Test')) {
                throw new \Exception('Does not return false');
            }
        });

        it('returns true if the value is in the array', function() {
            $matcher = new InclusionMatcher(array(2));
            if (!$matcher->match(array(1, 2, 3))) {
                throw new \Exception('Does not return true');
            }
        });

        it('returns false if the value is not in the array', function() {
            $matcher = new InclusionMatcher(array(4));
            if ($matcher->match(array(1, 2, 3))) {
                throw new \Exception('Does not return false');
            }
        });

        it("throws an exception if haystack isn't an array or string", function() {
            $exceptionThrown = false;
            try {
                $matcher = new InclusionMatcher(array('test'));
                $matcher->match(1);
            } catch (\Exception $exception) {
                $exceptionThrown = true;
            }

            if (!$exceptionThrown) {
                throw new \Exception('Does not throw an exception');
            }
        });
    });

    context('match all with multiple constructor arguments', function() {
        it('returns true if the substrings are found in the string', function() {
            $matcher = new InclusionMatcher(array('String', 'Test'));
            if (!$matcher->match('TestString')) {
                throw new \Exception('Does not return true');
            }
        });

        it('returns false if any substring is not found in the string', function() {
            $matcher = new InclusionMatcher(array('Test', 'String'));
            if ($matcher->match('Test')) {
                throw new \Exception('Does not return false');
            }
        });

        it('returns true if the values are in the array', function() {
            $matcher = new InclusionMatcher(array(1, 2));
            if (!$matcher->match(array(1, 2, 3))) {
                throw new \Exception('Does not return true');
            }
        });

        it('returns false if any value is not in the array', function() {
            $matcher = new InclusionMatcher(array(1, 4));
            if ($matcher->match(array(1, 2, 3))) {
                throw new \Exception('Does not return false');
            }
        });

        it('accepts any number of arguments', function() {
            $matcher = new InclusionMatcher(array(1, 2, 3, 4, 5, 6, 7, 8, 9));
            if ($matcher->match(array(1, 2, 3, 4, 5, 6, 7, 8, 10))) {
                throw new \Exception('Does not accept any number');
            }

            $matcher = new InclusionMatcher(array(1, 2, 3, 4, 5, 6, 7, 8, 9));
            if (!$matcher->match(array(1, 2, 3, 4, 5, 6, 7, 8, 9))) {
                throw new \Exception('Does not accept any number');
            }
        });
    });

    context('match any with multiple constructor arguments', function() {
        it('returns true if any substring is found in the string', function() {
            $matcher = new InclusionMatcher(array('spec', 'Test'), false);
            if (!$matcher->match('TestString')) {
                throw new \Exception('Does not return true');
            }
        });

        it('returns false if no substring is found', function() {
            $matcher = new InclusionMatcher(array('test', 'String'), false);
            if ($matcher->match('Test')) {
                throw new \Exception('Does not return false');
            }
        });

        it('returns true if any value is in the array', function() {
            $matcher = new InclusionMatcher(array(5, 2), false);
            if (!$matcher->match(array(1, 2, 3))) {
                throw new \Exception('Does not return true');
            }
        });

        it('returns false if no value is in the array', function() {
            $matcher = new InclusionMatcher(array(4, 5));
            if ($matcher->match(array(1, 2, 3))) {
                throw new \Exception('Does not return false');
            }
        });

        it('accepts any number of arguments', function() {
            $matcher = new InclusionMatcher(array(1, 2, 3, 4, 5, 6, 7, 8, 9));
            if ($matcher->match(array('a', 'b', 'c', 'd', 'e', 'f', 8))) {
                throw new \Exception('Does not accept any number');
            }

            $matcher = new InclusionMatcher(array(1, 2, 3, 4, 5, 6, 7, 8, 9));
            if ($matcher->match(array('a', 'b', 'c', 'd', 'e', 'f'))) {
                throw new \Exception('Does not accept any number');
            }
        });
    });

    context('getFailureMessage', function() {
        context('match all', function() {
            it('lists the expected type and needle', function() {
                $matcher = new InclusionMatcher(array('Testing', 'Refactoring'));
                $matcher->match('TestString');
                $expected = 'Expected string to contain Testing, Refactoring';

                if ($expected !== $matcher->getFailureMessage()) {
                    throw new \Exception('Did not return expected failure message');
                }
            });

            it('lists the expected type and needle with inversed logic', function() {
                $matcher = new InclusionMatcher(array('Test', 'String'));
                $matcher->match('TestString');
                $expected = 'Expected string not to contain Test, String';

                if ($expected !== $matcher->getFailureMessage(true)) {
                    throw new \Exception('Did not return expected failure message');
                }
            });
        });

        context('match any', function() {
            it('lists the expected type and needle', function() {
                $matcher = new InclusionMatcher(array('Testing', 'Specs'), false);
                $matcher->match('TestString');
                $expected = 'Expected string to contain one of Testing, Specs';

                if ($expected !== $matcher->getFailureMessage()) {
                    throw new \Exception('Did not return expected failure message');
                }
            });

            it('lists the expected type and needle with inversed logic', function() {
                $matcher = new InclusionMatcher(array('Test', 'String'), false);
                $matcher->match('TestString');
                $expected = 'Expected string not to contain one of Test, String';

                if ($expected !== $matcher->getFailureMessage(true)) {
                    throw new \Exception('Did not return expected failure message');
                }
            });
        });
    });
});
