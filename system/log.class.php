<?php

/**
 * Log - Theamus logging class
 * PHP Version 5.5.3
 * Version 1.3.0
 * @package Theamus
 * @link http://www.theamus.com/
 * @author ælieo (aelieo) <aelieo@theamus.com>
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
    protected function get_call_info() {
        $backtrace = debug_backtrace(); // Define all of the information for this call

        $call_info = array(); // Define the call information

        // Loop through all of the backtrace information
        foreach ($backtrace as $item) {
             // Ignore info that doesn't have the necessary data
            if (!isset($item['class']) || !isset($item['function']) || !isset($item['file']) || !isset($item['line'])) continue;

            // Only if the class has a name, it's not an included file and it's not this LOG file
            if ($item['class'] != '' && $item['function'] != 'include' && $item['file'] != __FILE__) {
                // Add the call information to the return array
                $call_info[] = array(
                    'class'     => $item['class'],
                    'function'  => $item['function'],
                    'line'      => $item['line'],
                    'file'      => $this->clean_file_path($item['file']));
            }
        }

        /**
         * This part is where things get tricky.
         *
         * The condition for the return is there to let the log know wether or
         *  not the call came from the inside of a file that's being run from
         *  Theme->content() or an actual class and function.
         *
         * Because of this, any logs that come from Theme won't be 100% accurate
         *  at the cost of knowing where the log was called in a feature file
         */
        return $call_info[1]['class'] == 'Theme' ? $call_info[0] : $call_info[1];
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
        $valid_types = array('general', 'developer', 'system', 'query');
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