<?php

/**
 * Log - Theamus logging class
 * PHP Version 5.5.3
 * @package Theamus
 * @link http://github.com/helllomatt/Theamus
 * @author MMT (helllomatt)
 */
class Log {
    protected $Theamus;
    protected $query_data;
    protected $logging_permission = array();


    /**
     * Connect to the Theamus system and define the logging settings
     *
     * @param object $t
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;

        if (!$this->Theamus->DB->connection) return;

        $this->logging_permission = explode(',', $this->Theamus->settings['logging']);
        return;
    }


    /**
     * Closes the class out, committing any logs that were logged
     *
     * @return
     */
    public function __destruct() {
        if (!$this->Theamus->DB->connection) return;
        $this->commit_logs();
    }
    

    /**
     * Commit any logs that were gathered to the database table
     *
     * @return
     */
    protected function commit_logs() {
        // Check for query data
        if (!empty($this->query_data)) {
            // Query the database, adding the information to the log table
            $this->Theamus->DB->insert_table_row(
                    $this->Theamus->DB->system_table('logs'),
                    $this->query_data);
        }

        return; // Return!
    }


    /**
     * Cleans the file path to be standard for the database table file value
     *
     * @param string $file
     * @return string
     */
    protected function clean_file_path($file) {
        $wo_root = str_replace(ROOT, '', $file);  // Clean the ROOT off of the file path

        $flip_slashes = str_replace('\\', '/', $wo_root);  // Flip the slashes from WINDOWS to *NIX

        $new_path = trim($flip_slashes, '/');  // Remove the leading slash, or any trailing slashes

        return $new_path; // Return the path like it should be!
    }
    

    /**
     * Gets the class name and function about where the log function was called from
     *
     * @return array $call_info
     */
    public function get_call_info() {
        // Get the trace of the call
        $e = new Exception();
        $trace = $e->getTrace();
        
        // Array for the functions to ignore
        $log_functions = array("general", "cli", "query", "developer", "system", "include");
        
        /* The way that the trace follows files back is strange. trace[0] will give the 
         * file and line that called this function, but not the function itself. All of the 
         * numbers greater than 0 will be the trace back from the file calling this function.
         */
        $file = $trace[0]['file'] == __FILE__ ? $trace[1]['file'] : $trace[0]['file'];
        $line = $trace[0]['file'] == __FILE__ ? $trace[1]['line'] : $trace[0]['line'];

        /* So, the class and functions are strange too. Because they aren't 0, or 1 in the trace 
         * (which 0 = this->get_called_info() and 1 = the logging function) the actual class 
         * and function name is stored in the 3rd call from this function. Weird, but consistent.
         */
        $class = !isset($trace[2]['class']) ? "" : $trace[2]['class'];
        $function = in_array($trace[2]['function'], $log_functions) ? "" : $trace[2]['function'];
        
        // Return the information about the call stack 
        return array('class'   => $class,
                    'function' => $function,
                    'line'     => $line,
                    'file'     => $this->clean_file_path($file));
    }


    /**
     * Adds the log information to the class log query data to be inserted into
     *  the database when the class destructs
     *
     * @param string $message
     * @param array $call_info
     * @throws Exception
     * @return
     */
    protected function add_log_query_data($message, $type, $call_info) {
        // Check if the message doesn't have a value
        if ($message == '') throw new Exception('"Message" is a required log variable and was found to be empty.');

        // Check for a valid type
        $valid_types = array('general', 'developer', 'system', 'query', 'cli');
        if ($type == '' || !in_array($type, $valid_types)) throw new Exception('The log type is invalid.');

        // Define the query data that has the log record information
        $this->query_data[] = array(
            'message'   => $message,
            'class'     => $call_info['class'],
            'function'  => $call_info['function'],
            'line'      => $call_info['line'],
            'file'      => $call_info['file'],
            'type'      => $type,
            'time'      => '[func]now()');

        return; // Return
    }


    /**
     * Handles the exceptions for the class
     *
     * @param Exception object $e
     * @return
     */
    protected function handle_exception($e) {
        $this->Theamus->notify('danger', '<strong>Theamus Log Error:</strong> '.$e->getMessage());
        return;
    }


    /**
     * Adds a general log record to the database
     *
     * @param sting $message
     * @return
     */
    public function general($message) {
        // Check if the site admins want to log general records
        if (in_array('general', $this->logging_permission)) {
            // Try to add the log to the query data or handle the error
            try { $this->add_log_query_data($message, 'general', $this->get_call_info()); }
            catch (Exception $e) { $this->handle_exception($e); }
        }

        return; // Return!
    }
    
    
    /**
     * Adds a general log record to the database
     *
     * @param sting $message
     * @return
     */
    public function cli($message) {
        // Check if the site admins want to log general records
        if (in_array('cli', $this->logging_permission)) {
            // Try to add the log to the query data or handle the error
            try { $this->add_log_query_data($message, 'cli', $this->get_call_info()); }
            catch (Exception $e) { $this->handle_exception($e); }
        }

        return; // Return!
    }


    /**
     * Adds a developer log record to the database
     *
     * @param sting $message
     * @return
     */
    public function developer($message) {
        // Check for the developer logs to be on
        if (in_array('developer', $this->logging_permission)) {
            // Try to add the log to the query data or handle the error
            try { $this->add_log_query_data($message, 'developer', $this->get_call_info()); }
            catch (Exception $e) { $this->handle_exception($e); }
        }

        return; // Return!
    }


    /**
     * Adds a system log record to the database
     *
     * @param sting $message
     * @return
     */
    public function system($message) {
        $call_info = $this->get_call_info(); // Define the call info

        // Define the classes that can call this function
        $system_classes = array(
            'Theamus',
            'API',
            'Call',
            'DB',
            'Files',
            'Install',
            'Log',
            'Pagination',
            'Theme',
            'User'
        );

        // Check for the calling class to be a system class
        if (in_array($call_info['class'], $system_classes)) {
            // Try to add the log to the query data or handle the error
            try { $this->add_log_query_data($message, 'system', $call_info); }
            catch (Exception $e) { $this->handle_exception($e); }
        }

        return; // Return!
    }


    /**
     * Adds a query log record to the database
     *
     * @param sting $message
     * @return
     */
    public function query($message) {
        // Check if the site admins want to log query records
        if (in_array('query', $this->logging_permission)) {
            // Try to add the log to the query data or handle the error
            try { $this->add_log_query_data($message, 'query', $this->get_call_info()); }
            catch (Exception $e) { $this->handle_exception($e); }
        }

        return; // Return!
    }
}