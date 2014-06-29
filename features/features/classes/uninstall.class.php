<?php

class FeatureUninstall {
    public $config = array();

    public function __construct() {
        // Define class variables
        $this->initialize_variables();
    }

    public function __destruct() {
        // Disconnect from the database
        $this->tDataClass->disconnect();
    }

    private function initialize_variables() {
        // Define the data class and connect to the database
        $this->tDataClass           = new tData();
        $this->tData                = $this->tDataClass->connect();
        $this->tDataClass->prefix   = DB_PREFIX;

        // Define the features class
        $this->Features = new Features();
    }

    public function system_table_data($table = "", $key = "", $value = "") {
        // Check the incoming values
        if ($table == "" || $key == "" || $value == "") throw new Exception("One or more values to uninstall the table data is invalid.");
        
        // Write the query to run
        $query = "DELETE FROM `".$this->tDataClass->prefix."$table` WHERE `$key`='$value';";
        
        // Add the query to the global query array
        $this->uninstall_sql[] = $query;
    }
    
    public function query($query = "") {
        // Check the data
        if ($query == "") throw new Exception("The custom query being run cannot be blank.");
        
        // Add the query to the global sql
        $this->uninstall_sql[] = trim($query, ";").";";
    }

    
    public function get_uninstall_sql() {
        return $this->uninstall_sql;
    }
}