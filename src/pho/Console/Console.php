<?php

namespace pho\Console;

use pho\Reporter;

class Console
{
    const VERSION = '0.1.0';

    public  $formatter;

    public  $options;

    private $optionParser;

    private $paths;

    private $errorStatus;

    private $availableOptions = array(
        'ascii'     => array('--ascii',     '-a', 'Show ASCII art on completion'),
        'help'      => array('--help',      '-h', 'Output usage information'),
        'filter'    => array('--filter',    '-f', 'Run specs containing a pattern', 'pattern'),
        'reporter'  => array('--reporter',  '-r', 'Specify the reporter to use', 'name'),
        'stop'      => array('--stop',      '-s', 'Stop on failure'),
        'version'   => array('--version',   '-v', 'Display version number'),
        'watch'     => array('--watch',     '-w', 'Watch files for changes and rerun specs'),
        'namespace' => array('--namespace', '-n', 'Only use namespaced functions'),

        // TODO: Implement options below
        // 'no-color'  => ['--no-color',  '-n', 'Disable terminal colors'],
        // 'generate'  => ['--generate',  '-g', 'Generate suites for classes in path', 'path'],
        // 'bootstrap' => ['--bootstrap', '-b', 'Bootstrap file to load', 'bootstrap'],
        // 'processes' => ['--processes', '-p', 'Number of processes to use', 'processes'],
        // 'verbose'   => ['--verbose',   '-V', 'Enable verbose output']
    );

    private $reporters = array('dot', 'spec', 'list');

    private $defaultDirs = array('test', 'spec');

    private $stream;

    /**
     * The constructor stores the arguments to be parsed, and creates instances
     * of both ConsoleFormatter and ConsoleOptionParser. Also, if either a test
     * or spec directory exists, they are set as the default paths to traverse.
     *
     * @param array  $arguments An array of argument strings
     * @param string $stream    The I/O stream to use when writing
     */
    public function __construct($arguments, $stream)
    {
        $this->arguments = $arguments;
        $this->options = array();
        $this->paths = array();
        $this->stream = fopen($stream, 'w');

        $this->formatter = new ConsoleFormatter();
        $this->optionParser = new ConsoleOptionParser();

        // The default folders to look in are ./test and ./spec
        foreach ($this->defaultDirs as $dir) {
            if (file_exists($dir) && is_dir($dir)) {
                $this->paths[] = $dir;
            }
        }
    }

    /**
     * Returns the namespaced name of the reporter class requested via the
     * command line arguments, defaulting to DotReporter if not specified.
     *
     * @return string The namespaced class name of the reporter
     */
    public function getReporterClass()
    {
        $reporter = $this->options['reporter'];

        if (!$reporter || !in_array($reporter, $this->reporters)) {
            return 'pho\Reporter\DotReporter';
        }

        $reporterClass = ucfirst($reporter) . 'Reporter';
        return "pho\\Reporter\\$reporterClass";
    }

    /**
     * Returns an array of strings corresponding to file and directory paths
     * to be traversed.
     *
     * @return array An array of paths
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Returns the error status that should be used to exit after parsing,
     * otherwise it returns null.
     *
     * @return mixed An integer error status, or null
     */
    public function getErrorStatus()
    {
        return $this->errorStatus;
    }

    /**
     * Parses the arguments originally supplied via the constructor, assigning
     * their values to the option keys in the $options array. If the arguments
     * included the help or version option, the corresponding text is printed.
     * Furthermore, if the arguments included a non-valid flag or option, or
     * if any of the listed paths were invalid, error message is printed.
     */
    public function parseArguments()
    {
        // Add the list of options to the OptionParser
        foreach ($this->availableOptions as $name => $desc) {
            $desc[3] = (isset($desc[3])) ? $desc[3] : null;
            list($longName, $shortName, $description, $argumentName) = $desc;

            $this->optionParser->addOption($name, $longName, $shortName,
                $description, $argumentName);
        }

        // Parse the arguments, assign the options
        $this->optionParser->parseArguments($this->arguments);
        $this->options = $this->optionParser->getOptions();

        // Verify the paths, listing any invalid paths
        $paths = $this->optionParser->getPaths();
        if ($paths) {
            $this->paths = $paths;

            foreach ($this->paths as $path) {
                if (!file_exists($path)) {
                    $this->errorStatus = 1;
                    $this->writeLn("The file or path \"{$path}\" doesn't exist");
                }
            }
        }

        // Render help or version text if necessary, and display errors
        if ($this->options['help']) {
            $this->errorStatus = 0;
            $this->printHelp();
        } elseif ($this->options['version']) {
            $this->errorStatus = 0;
            $this->printVersion();
        } elseif ($this->optionParser->getInvalidArguments()) {
            $this->errorStatus = 1;
            foreach ($this->optionParser->getInvalidArguments() as $invalidArg) {
                $this->writeLn("$invalidArg is not a valid option");
            }
        }
    }

    /**
     * Outputs a single line, replacing all occurrences of the newline character
     * in the string with PHP_EOL for cross-platform support.
     *
     * @param string $string The string to print
     */
    public function write($string)
    {
        fwrite($this->stream, str_replace("\n", PHP_EOL, $string));
    }

    /**
     * Outputs a line, followed by a newline, while replacing all occurrences of
     * '\n' in the string with PHP_EOL for cross-platform support.
     *
     * @param string $string The string to print
     */
    public function writeLn($string)
    {
        $this->write($string);
        fwrite($this->stream, PHP_EOL);
    }

    /**
     * Outputs the help text, as required when the --help/-h flag is used. It's
     * done by iterating over $this->availableOptions.
     */
    private function printHelp()
    {
        $this->writeLn("Usage: pho [options] [files]\n");
        $this->writeLn("Options\n");

        // Loop over availableOptions, building the necessary input for
        // ConsoleFormatter::alignText()
        $options = array();
        foreach ($this->availableOptions as $name => $optionInfo) {
            $row = array($optionInfo[1], $optionInfo[0]);
            $row[] = (isset($optionInfo[3])) ? "<{$optionInfo[3]}>" : '';
            $row[] = $optionInfo[2];

            $options[] = $row;
        }

        $pad = str_repeat(' ', 3);
        foreach ($this->formatter->alignText($options, $pad) as $line) {
            $this->writeLn($pad . $line);
        }
    }

    /**
     * Outputs the version information, as defined in the VERSION constant.
     */
    private function printVersion()
    {
        $this->writeLn('pho version ' . self::VERSION);
    }
}
