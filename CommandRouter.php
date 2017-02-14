<?php

/**
 * Created by PhpStorm.
 * User: mzieba <admin@hadriel.net>
 * Date: 13.02.17
 * Time: 12:17
 */
class CommandRouter
{
    const ROUTE_ACTION_DELIMITER = '-';

    /**
     * @var AbstractCommand[]
     */
    private static $commands;

    /**
     * @var string
     */
    private $route;

    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var array
     */
    private $options;

    public function __construct($argv)
    {
        unset($argv[0]);

        if (empty($argv)) {
            static::printHelp();
            exit();
        }

        // Parse arguments
        foreach ($argv as $argument) {
            // Argument is not empty
            if (!empty($argument)) {
                // Argument is an option
                if ($argument[0] === '-') {
                    $this->options[] = $argument;
                } // Argument is a route
                elseif (empty($this->route)) {
                    // Found '-', need to explode routing
                    if (strpos($argument, self::ROUTE_ACTION_DELIMITER) !== false) {
                        $routing = explode(self::ROUTE_ACTION_DELIMITER, $argument, 2);
                        $this->route = $routing[0];
                        $this->action = $routing[1];
                    } else {
                        $this->route = $argument;
                        $this->action = 'run';
                    }
                } // Argument is an argument
                else {
                    $this->arguments[] = $argument;
                }
            }
        }
    }

    public function register($route, AbstractCommand $command)
    {
        // Add new command
        static::$commands[$route] = $command;
        return $this;
    }

    public function run()
    {
        // Check if we have routing, if not, print general help
        $this->dispatchRoute();
        // Execute command with params
    }

    private function dispatchRoute()
    {
        // No commands registered
        if (empty(static::$commands)) {
            static::printError('No commands registered');
        }

        // No commands provided
        if (empty($this->route)) {
            static::printError('No commands provided');
        }

        // Command not found
        if (!array_key_exists($this->route, static::$commands)) {
            static::printError('Command ' . $this->route . ' not found');
        }

        // Action in command not exists
        if (!method_exists(static::$commands[$this->route], $this->action)) {
            static::printError('Command not found: ' . $this->route . self::ROUTE_ACTION_DELIMITER . $this->action);
        }

        // Run command
        static::$commands[$this->route]->{$this->action}(...$this->arguments);
    }

    protected static function printHelp()
    {
        echo 'General usage: ' . ' filename.php [--options] [command]' . PHP_EOL;
        echo 'Available commands: ' . PHP_EOL;
        foreach (static::$commands as $route => $command) {
            $commandReflection = new ReflectionObject($command);
            $methods = $commandReflection->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                echo "\t" .
                    $route .
                    static::ROUTE_ACTION_DELIMITER .
                    $method->getName() .
                    ' '.
                    static::getMethodParametersForPrint($method->getParameters()),
                    ' - ' .
                    static::getDocCommentSummary($method->getDocComment()) .
                    PHP_EOL;
            }
        }
    }

    protected static function printError($error)
    {
        echo 'ERROR: ' . $error . PHP_EOL;
        static::printHelp();
        exit();
    }

    protected static function getDocCommentSummary($doc)
    {
        if (preg_match('/\/\*\*\s*\n\s*\*\s*(.*?)\n/i', $doc, $matches) != false) {
            return $matches[1];
        }
    }

    protected static function getMethodParametersForPrint($params)
    {
        if (empty($params)) {
            return null;
        }


        foreach ($params as $param) {
            $forPrint[] = '['.$param->getName().']';
        }

        return implode(' ', $forPrint);
    }
}
