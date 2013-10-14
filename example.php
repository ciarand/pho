<?php

require('src/pho/pho.php');

describe('MyClass', function() {
    before(function() {
        // echo "Before\n";
    });

    after(function() {
        // echo "After\n";
    });

    beforeEach(function() {
        // echo "Top BeforeEach\n";
    });

    afterEach(function() {
        // echo "Top AfterEach\n";
    });

    describe('When my class is created', function() {
        beforeEach(function() {
            // echo "beforeEach\n";
        });

        it('should echo a number', function() {
            // echo "spec 1\n";
        });

        it('should echo a second number', function() {
            // echo "spec 2\n";
        });

        it('should throw an exception', function() {
            throw new Exception('Something went wrong');
        });

        describe('Third-level nested suite', function() {
            it('Testing deeply nested', function() {
                // echo "deeply nested";
            });

            it('Should throw an error', function() {
                trigger_error('Some error', E_USER_ERROR);
            });
        });

        afterEach(function() {
            // echo "afterEach\n";
        });
    });

    it('Last spec, within the first suite', function() {
        // echo "Last spec\n";
    });
});

pho\Runner::run();