<?php

class FeatureInstall {
    private $install_sql = array();
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

    public function create_table($table, $columns) {
        // Query the database for this table's existance
        $query = $this->tData->query("SHOW TABLES LIKE '$table'");
        if ($query->num_rows == 1) return;

        // Create the query to run to create this table
        foreach ($columns as $column) {
            if (!is_array($column)) $table_columns[] = $column;
            else $table_columns[] = implode(" ", $column);
        }
        $create_query = "CREATE TABLE `$table` (".implode(", ", $table_columns).");";

        // Add the query to the global install sql
        $this->install_sql[] = $create_query;
    }

    private function define_data($data) {
        // Loop through all of the data
        foreach ($data as $key => $val) {
            // If it's an array, recurse!
            if (is_array($val) && !empty($val)) {
                $temp[] = $this->define_data($val);
            } else {
                // Add the key/value combination to be returned
                $temp['keys'][] = "`".$key."`";
                $temp['vals'][] = "'".$this->tData->real_escape_string($val)."'";
            }
        }

        // Return the data
        return $temp;
    }

    public function table_data($table, $data) {
        // Define the data to be sql friendly
        $data = $this->define_data($data);
        if (count($data) == 2) $data = array($data);

        // Loop through all of the data
        foreach ($data as $item) {
            // Add the query to the global install sql
            $this->install_sql[] = "INSERT INTO `$table` (".implode(", ", $item['keys']).") VALUES (".implode(", ", $item['vals']).");";
        }
    }

    public function permissions($p) {
        // Loop through all of the permissions
        foreach ($p as $item) {
            // Add the query to the global install sql
            $this->install_sql[] = "INSERT INTO `".$this->tDataClass->prefix."permissions` (`feature`, `permission`) VALUES ('".$this->config['alias']."', '".$this->tData->real_escape_string($item)."');";
        }
    }

    public function group($alias = "", $name = "", $permissions = "", $home = "false") {
        // Check the requirements
        if ($alias == "") throw new Exception("The alias to the group being created cannot be blank.");
        if ($name == "") throw new Exception("The name to the group being created cannot be blank.");

        // Sanitize the variables to be db friendly
        $alias = $this->tData->real_escape_string($alias);
        $name = $this->tData->real_escape_string($name);
        $permissions = $this->tData->real_escape_string($permissions);
        $home = $this->tData->real_escape_string($home);

        // Create the query to add this group
        $query = "INSERT INTO `".$this->tDataClass->prefix."groups` (`alias`, `name`, `permissions`, `permanent`, `home_override`) VALUES ('$alias', '$name', '$permissions', 0, '$home');";

        // Add the query to the global install sql
        $this->install_sql[] = $query;
    }
    
    public function modify_table($table = "", $ar = "add", $column = "", $args = "") {
        // Check the requirements
        if ($table == "") throw new Exception("The table to be modified cannot be blank.");
        if ($column == "") throw new Exception("The column to be modified cannot be blank.");
        
        // Check the database for this table column's existance
        $check_query = $this->tData->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE `table_name`='$table' AND `column_name`='$column'");
        
        if ($check_query->num_rows == 0 || $ar != "add") {
            // Create the query
            $query = "ALTER TABLE `$table` ".$ar." `".$column."` ".$args;

            // Add the query to the global install sql
            $this->install_sql[] = trim($query).";";
        }
    }
    
    public function query($query = "") {
        // Check the data
        if ($query == "") throw new Exception("The custom query being run cannot be blank.");
        
        // Add the query to the global install sql
        $this->install_sql[] = trim($query, ";").";";
    }

    public function get_install_sql() {
        return $this->install_sql;
    }
}