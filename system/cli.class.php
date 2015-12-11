<?php

/**
 * CLI - Theamus Command Line Interface handling class
 * PHP Version 5.5.3
 * @package Theamus
 * @link http://github.com/helllomatt/Theamus
 * @author MMT (helllomatt)
 */
class CLI {
    /**
     * Theamus object
     * 
     * @var object $Theamus
     */
    private $Theamus;
    
    
    /**
     * Hold the information about the feature (name, path);
     * 
     * @var string $feature
     */
    private $feature;
    
    
    /**
     * An array of all the registered commands for the requested features
     * 
     * @var array $commands
     */
    private $commands;
    
    
    /**
     * Hello, how are you command line?
     * 
     * @param object $t The Theamus object
     */
    public function __construct($t) { $this->Theamus = $t; }
    
    
    /**
     * Print out to the command line
     * 
     * @param string $message Message to print out to the command line
     * @param string $tab Precursor padding to the output
     */
    public function out($message, $tab = "\t") {
        print_r("{$tab}".print_r($message, true).PHP_EOL);
    }
    
    
    /**
     * Collects input from the command line 
     * 
     * @param string $message Message to ask a question that will be answered
     * @param string $tab Precursor padding to the output
     * @returns mixed readline()
     */
    public function in($message, $tab = "\t") {
        return readline($tab.$message." ");
    }
    
    
    /**
     * Error out the CLI program
     * 
     * @param string $message Message to print out to the command line
     */
    public function error_out($message) {
        print_r("\tERROR: ".print_r($message, true).PHP_EOL);
        die();
    }
    
    
    /**
     * Run the Theamus CLI program
     * 
     * @param string[] $argv Command line parameters passed to Theamus
     */
    public function run($argv) {
        // Check for any arguments
        if (count($argv) <= 1) $this->error_out("No command was given to run!");
        
        // Get the feature information and register the commands
        $this->get_feature($argv[1]);
        $this->get_config();

        // Remove the feature information from the CLI arguments        
        for ($i = 0; $i < 2; $i++) array_shift($argv);
        
        // Get the command and then run it
        $this->get_command($argv);
    }
    
    
    /**
     * Check to make sure the command being requested is one that has been
     * registered with the feature
     * 
     * @param srting[] $args Array of command name + arguments to pass to it
     */
    public function get_command($args) {
        // Check if a command was given
        if (empty($args)) $this->error_out("No command was given to run.");
        
        // Check if any commands were registered
        if (empty($this->commands)) $this->error_out("No commands were registered to run.");
       
        // Check if the command was registered
        if (!array_key_exists($args[0], $this->commands)) {
            $this->error_out("The command you're trying to run isn't registered.");
        }
        
        // Define the command, shift the arguments to exclude the command
        $command = $args[0];
        array_shift($args);

        // Run it        
        $this->run_command($this->commands[$command], $args);
    }
    
    
    /**
     * Get the arguments from the command line and make them more usable
     * 
     * @param string[] $args Array of arugments passed from the command line
     */
    private function get_args($args) {
        if (empty($args)) return array();
        
        // Loop through all of the arguments defining them into a key => value array
        $ret = array();
        foreach ($args as $arg) {
            $keyval = explode("=", $arg);
            
            // Define the key and the value
            $key = $keyval[0];
            array_shift($keyval);
            $value = implode("=", $keyval);
            
            $ret[$key] = $value;
        }
        
        return $ret;
    }
    
    
    /**
     * Runs the command
     * 
     * @param string $command Name of the command to run 
     * @param string[] $arg Array of information about the registered command
     */
    private function run_command($command, $arg) {
        // Get the arguments from the command line
        $args = $this->get_args($arg);
        
        // If the command is a class method, run it and handle errors
        if ($command['type'] == "class") {
            $class = ${$command['info'][1]} = new $command['info'][1]($this->Theamus);
            try { $response = call_user_func(array($class, $command['info'][2]), $args); }
            catch (Exception $e) { $this->error_out($e->getMessage()); }
            
        // If the command is a function, run it and handle errors
        } elseif ($command['type'] == "function") {
            try { $response = $command['info'][1]($this->Theamus, $args); }
            catch (Exception $e) { $this->error_out($e->getMessage()); }
        }
    }
    
    
    /**
     * Check to see if the feature being called on actually exists
     * 
     * @param string requested Name of the requested feature
     */
    public function get_feature($requested = "") {
        // Define the path to the feature
        $path = $this->Theamus->file_path(ROOT."/features/{$requested}/");

        // Check if the feature exists        
        if (!is_dir($path)) $this->error_out("The feature {$requested} does not exist.");
        
        // Define the feature information
        $this->feature = array("path" => $path, "name" => $requested);
    }
    

    /**
     * Loads the feature configuration file to register eligable $commands
     */
    public function get_config() {
        // Define the path to the feature configuration file
        $path = $this->Theamus->file_path($this->feature['path']."/config.php");
        if (!file_exists($path)) $this->error_out("Failed to find config.php in the feature folder.");
        
        $Theamus = $this->Theamus;
        
        // Include the configuration file which should register commands
        include_once($path);
    }
    
    
    /**
     * Registers all of the commands for the feature that can be run 
     * 
     * @param string $command_name Name of the command to register 
     * @param string[] $info Array of information on how to run the command
     */
    public function register_command($command_name, $info) {
        if (!$this->Theamus->using_cli) return;
        
        // Define and check the file path for existence
        $command_file_path = $this->Theamus->file_path($this->feature['path'].$info[0]);
        if (!file_exists($command_file_path)) $this->error_out("The command file {$info[0]} does not exist.");
        
        // Include the file
        include_once($command_file_path);
        
        // Check if the given information is a class
        if (!class_exists($info[1])) {
            // Check if the given information is a function
            if (!function_exists($info[1])) $this->error_out("The function {$info[1]} does not exist!");
            else {
                $this->commands[$command_name] = array("type" => "function", "info" => $info);
                return;
            }
            
            // Neither a class or function, die.            
            $this->error_out("The class {$info[1]} does not exist!");
        }
        
        // Check if the class method exists
        if (!method_exists($info[1], $info[2])) $this->error_out("The method {$info[2]} does not exist!");

        // Add the command to the commands list
        $this->commands[$command_name] = array("type" => "class", "info" => $info);
    }
}