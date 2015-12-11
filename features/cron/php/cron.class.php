<?php

class Cron {
    private $Theamus;
    
    
    /**
     * Creates the class, connects to Theamus 
     * 
     * @var object $t Object of Theamus 
     */
    public function __construct($t) { $this->Theamus = $t; }
    
    
    /**
     * Adds a job to the Cron database table so that it can be run 
     * 
     * @var string $feature The name of the feature that holds the command 
     * @var string $command The name of the command to run 
     * @var int $recurrence Amount of time IN SECONDS that the job should run every
     * @var mixed[] $args Arguments that will be passed along to the command when it is being run
     * @throws Exception if no feature was defined
     * @throws Exception if no command was defined
     * @throws Exception if the query to add the job failed
     * @returns true
     */
    private function add_job($feature = "", $command = "", $recurrence = 1, $args = array()) {
        // Check for valid variaables
        if ($feature == "") throw new Exception("Failed to add cron job because no feature was defined.");
        if ($command == "") throw new Exception("Failed to add cron job because no command was defined.");
        
        // Add the inforamtion to the database
        $query = $this->Theamus->DB->insert_table_row(
            $this->Theamus->DB->system_table("crons"),
            array("feature"     => $feature,
                "command"       => $command,
                "arguments"     => json_encode($args),
                "recurrence"    => $recurrence));
                
        // Check the query for errors, fail out if there are any
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to add cron job because of a query error.");
        }
        
        return true; // Done!
    }
    
    
    /**
     * Gets cron job information from the database based on the feature/command
     * 
     * @var string $feature Name of the feature to get jobs related to 
     * @var string $command Name of the command to get jobs related to
     * @throws Exception if no feature was defined
     * @throws Exception if the query failed to get job information
     * @returns array of job information from the database
     */
    private function get_job($feature = "", $command= "") {
        // Check to make sure a feature was given
        if ($feature == "") throw new Exception("Failed to get cron job because no feature was given to look for.");
        
        // Define the conditions here, so that options can be added later
        $conditions = array("feature" => $feature);

        // Add the command to the query conditions if one was defined        
        if ($command != "") $conditions['command'] = $command;
        
        // Query the database for jobs
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table("crons"),
            array(),
            array("operator" => "&&",
                "conditions" => $conditions));

        // Check the query for errors, fail out if there were any                
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to get cron job because of a query error.");
        }
        
        // Check to see if there were any results from the query
        if ($this->Theamus->DB->count_rows($query) === 0) return array();

        // Return the result information        
        $results = $this->Theamus->DB->fetch_rows($query);
        return isset($results[0]) ? $results : array($results);
    }
    
    
    /**
     * Deletes a cron job from the database
     * 
     * @var int $id ID of the job to delete
     * @throws Exception if no id was given
     * @throws Exception if the query failed to delete the job 
     * @returns true
     */
    private function delete_job($id = 0) {
        
        // Check for a valid ID
        if ($id == 0) throw new Exception("Failed to delete cron job because no ID was given.");
        
        // Query the database to delete the cron job
        $query = $this->Theamus->DB->delete_table_row(
            $this->Theamus->DB->system_table("crons"),
            array("operator" => "", "conditions" => array("id" => $id)));

        // Check the query for errors, fail if there are any                
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to delete cron job because of a query error.");
        }
        
        return true;
    }
    
    
    /**
     * Gets all of the cron jobs from the database
     * 
     * @throws Exception if the query failed getting jobs
     * @returns array of job information
     */
    private function get_all_jobs() {
        // Query the database for all of the jobs
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table("crons"));
        
        // Check the query for errors, fail out if there are any
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to get all of the jobs because of a query error.");
        }
        
        // Check for any jobs
        if ($this->Theamus->DB->count_rows($query) == 0) return array();
        
        // Return the job information
        $results = $this->Theamus->DB->fetch_rows($query);
        return isset($results[0]) ? $results : array($results);
    }
    
    
    /**
     * Sets the job activity to 1 so that it doesn't run more than once at a time 
     * 
     * @var mixed[] $job Job information
     * @var int $active 1 or 0 determining the job's current status
     * @throws Exception if there us a query error when updating
     * @returns true 
     */
    private function set_job_activity($job, $active = 0) {
        // Query the database, updating the information
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table("crons"),
            array("active" => $active),
            array("operator" => "", "conditions" => array("id" => $job['id'])));
            
        // Check the query for errors, fail out if there are any
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            $this->Theamus->Log->cli("Failed to update the job activity because of a query error. Job ID: {$job['id']}");
            throw new Exception("Failed to update the job activity because of a query error.");
        }
        
        return true;
    }
    
    
    /**
     * Updates the last run time once a job has finished so that it can be run again
     * 
     * @var mixed[] $job Job information
     * @throws Exception if there is a query error when updating
     * @returns true 
     */
    private function update_last_run_time($job) {
        // Query the database, updating the job information
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table("crons"),
            array("active" => 1, "last_run_time" => "[func]now()"),
            array("operator" => "", "conditions" => array("id" => $job['id'])));
            
        // Check the query for errors, fail out if there are any
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            $this->Theamus->Log->cli("Failed to update the last run time because of a query error. Job ID: {$job['id']} ");
            throw new Exception("Failed to update the last run time because of a query error.");
        }
        
        return true;
    }
    
    
    /**
     * Runs a cron job through Theamus CLI
     * 
     * @var mixed[] $job Job information
     */
    private function run_job($job) {
        // Define the information for the CLI that would otherwise be argv
        $run_argv = array("", $job['feature'], $job['command']);

        // Get the arguments as if they were passed through argv        
        $args = json_decode($job['arguments']);
        foreach ($args as $key => $value) $run_argv[] = $key."=".$value;
     
        // Set the job activity, run it, then update the run time information
        $this->update_last_run_time($job);
        $this->Theamus->CLI->run($run_argv);
        $this->set_job_activity($job, 0);
    }
    
    
    /**
     * The CLI command function to run all available cron jobs 
     */
    public function run_cron_jobs() {
        // Get all of the jobs from the database
        $jobs = $this->get_all_jobs();
        
        // Loop through all of the jobs to determine which ones should be run
        foreach ($jobs as $job) {
            // Do the math to get the last run time and the next run time
            $lrt = strtotime($job['last_run_time']);
            $nrt = $lrt + ($job['recurrence'] * 60);
            $current_time = time(); // minus five for buffer
            
            $this->Theamus->CLI->out(date("d-m-Y g:i s", $lrt));
            $this->Theamus->CLI->out(date("d-m-Y g:i s", $nrt));
            $this->Theamus->CLI->out(date("d-m-Y g:i s", $current_time));
            
            // If the job can be run, run it.
            if (($current_time + 5) >= $nrt && $job['active'] == 0) {
                $this->Theamus->CLI->out("run job");
                $this->run_job($job);
            }
        }
    }
    
    
    /**
     * Allows you to add cron jobs through the command line
     * 
     * @var mixed[] $args Arguments passed from the CLI 
     */
    public function cli_add_job($args) {
        // Define the defaults for everything
        $feature = $command = "";
        $recurrence = 1;
        
        // Check if a feature was defined, delete it from the args
        if (array_key_exists("feature", $args)) {
            $feature = $args['feature'];
            unset($args['feature']);
        }

        // Check if a command was defined, delete it from the args        
        if (array_key_exists("command", $args)) {
            $command = $args['command'];
            unset($args['command']);
        }
        
        // Check if a recurrence time was defined, delete it from the args
        if (array_key_exists("recurrence", $args)) {
            $recurrence = $args['recurrence'];
            unset($args['recurrence']);
        }

        // Add the job to the database        
        $this->add_job($feature, $command, $recurrence, $args);
        $this->Theamus->CLI->out("{$command} added to Theamus Cron Jobs");
    }
    
    
    /**
     * Gets a listing of jobs to print out to the command line for management
     */
    public function cli_get_jobs() {
        // Get all of the jobs
        $jobs = $this->get_all_jobs();

        // Say there are no jobs if there really aren't any        
        if (empty($jobs)) $this->Theamus->CLI->out("There are no jobs.");
        else {
            // Loop through all of the jobs showing their information
            foreach ($jobs as $job) {
                $this->Theamus->CLI->out("Job ID: {$job['id']} - ".
                    "Feature: {$job['feature']} - Command: {$job['command']} - ".
                    "Arguments: {$job['arguments']}");
            }
        }
    }
    
    /**
     * Deletes a job from the database via the command line
     * 
     * @var mixed[] $args Arguments passed from the command line
     */
    public function cli_delete_job($args) {
        // Define the ID, delete the job and say what you did.
        $id = array_key_exists("id", $args) ? $args['id'] : 0;
        $this->delete_job($id);
        $this->Theamus->CLI->out("Job deleted.");
    }
}