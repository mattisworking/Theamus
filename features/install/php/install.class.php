<?php

class Install {
    private $sql_structure  = "structure.sql";
    private $sql_data       = "data.sql";
    private $version        = "1.2";

    /**
     * Simplifies na API return
     *
     * @param boolean $error
     * @param array $data
     * @return array
     */
    private function define_return($error = false, $data = array()) {
        return array("error" => $error, "data" => $data);
    }


    /**
     * Checks a required array of keys/values against the provided aruments for existence and value
     *
     * @param array $args
     * @return array
     */
    private function check_args($required_args, $args) {
        foreach ($required_args as $key => $value) {
            // Check that the argument was submitted with the form
            if (!isset($args[$value])) {
                return $this->define_return(true, "The field <strong>$key</strong> is missing from the recieved arguments.");
            }

            // Check that the argument has a value
            if ($args[$value] == "") {
                return $this->define_return(true, "The field <strong>$key</strong> is required and cannot be blank.");
            }
        }

        return $this->define_return(false, "");
    }


    /**
     * Defines the queries to perform that will build the structure of the Theamus database
     *
     * @return string
     */
    private function define_queries($table_prefix, $for) {
        // Define the sql structure file path
        $file_path = path(ROOT."/features/install/sql/".$for);

        // Define the contents of the file
        $lines = file($file_path);

        // Define empty arrays that will be filled
        $queries = $temp_query = array();

        // Loop through the contents of the SQL structure file
        foreach ($lines as $line) {
            // Ignore comments and blank lines
            if (substr($line, 0, 2) == "--" || $line == "") {
                continue;
            }

            // Add the table prefix to the table name
            if (strpos($line, "CREATE TABLE") !== false || strpos($line, "INSERT IGNORE INTO") !== false) {
                // Find the table name
                preg_match("/`(.*?)`/", $line, $matches);
                $table_name = $matches[1];

                // Only bother doing this if there is the default prefix
                if (strpos($table_name, "tm_")){
                    // Define the table name without the default prefix
                    if (strpos($table_name, "_") !== false) {
                        $table = explode("_", $table_name);
                        $line = str_replace($table_name, $table_prefix.$table[1], $line);
                    } else {
                        $line = str_replace($table_name, $table_prefix.$table_name, $line);
                    }
                }
            }

            // Change the feature table prefixes to the defined prefix
            if (strpos($line, "'tm_'") !== false) {
                // Replace the default table prefix with the one defined
                $line = str_replace("tm_", $table_prefix, $line);
            }

            // Define the line as a part of the current query
            $temp_query[] = trim(trim($line, "\r\n"));

            // Define the complete query at the end of the sql line
            if (substr(trim($line), -1, 1) == ";") {
                $queries[] = trim(implode(" ", $temp_query));
                $temp_query = array(); // reset for a new query
            }
        }

        return implode("", $queries);
    }


    /**
     * Removes all things that might have been installed with the installation,
     * providing a clean slate
     *
     * @return
     */
    private function restart_installation() {
        // Connect to the database and gain access to the Theamus data class
        $tData = $this->database_connect();

        // Check the connection
        if ($tData == false) {
            return $this->define_return(true, "There was an error while trying to connect to the database.");
        }

        // Get all of the tables from the database
        $table_query = $tData->custom_query("SHOW TABLES");

        // Check the query and define the tables
        if ($table_query != false) {
            $results    = $tData->fetch_rows($table_query, "", PDO::FETCH_NUM);
            $tables     = isset($results[0]) ? $results : array($results);

            $drop_queries = array();

            // Loop through the tables, defining their drop queries
            foreach ($tables as $table) {
                $drop_queries[] = "DROP TABLE `$table[0]`;";
            }

            // Perform the drop queries, if there are any
            if (!empty($drop_queries)) {
                $tData->custom_query(implode(" ", $drop_queries));
            }
        }

        // Delete the configuration file
        $config_file = path(ROOT."/config.php");
        if (file_exists($config_file)) {
            unlink($config_file);
        }

        return;
    }


    /**
     * Attempts to connect to the database and return the Theamus Data class
     *
     * @return $tData|boolean
     */
    private function database_connect() {
        // Open a new database connection using the tData class
        $tData = new tData();
        $tData->db = $tData->connect(true);

        // Check for a successfull connection and return
        if ($tData->db == false) {
            return false;
        }
        return $tData;
    }


    /**
     * Validates information given by a form for the database configuration settings
     *
     * @param array $args
     * @return array
     */
    public function check_database_configuration($args) {
        // Check for empty arguments
        if (empty($args)) {
            return false;
        }

        // Check for required arguments
        $required_args = array(
            "Database Host"     => "database-host",
            "Login Username"    => "database-login-username",
            "Login Password"    => "database-login-password",
            "Database Name"     => "database-name",
            "Table Prefix"      => "database-table-prefix");
        $check_args = $this->check_args($required_args, $args);

        // Handle the result from the check arguments function
        if ($check_args['error'] == true) {
            return $check_args;
        }

        // Check the table prefix
        $table_prefix = $args['database-table-prefix'];

        // Table prefix length
        if (strlen(trim($table_prefix, "_")) > 7 || strlen(trim($table_prefix, "_")) < 2) {
            return $this->define_return(true, "The table prefix must be between 2 and 7 characters, not including the trailing underscore.");
        }

        // Table prefix underscores
        if (preg_match("/[^A-Za-z0-9]/i", trim($table_prefix, "_"))) {
            return $this->define_return(true, "The table prefix must be alphanumeric, not including the trailing underscore.");
        }

        // Trailing underscore
        if (substr($table_prefix, -1) != "_") {
            $args['database-table-prefix'] = $table_prefix."";
        }

        // Return the information
        return $this->define_return(false, json_encode($args));
    }


    /**
     * Attempts to connect to a database using PHP PDO MySQL with the given information from before
     *
     * @param array $args
     * @return array
     */
    public function check_database_connection($args) {
        $args = $args['config']; // shortening

        // Try to connect to the database
        try {
            // Connect/disconnect
            $test_connection = new PDO("mysql:host=".$args['database-host'].";dbname=".$args['database-name'], $args['database-login-username'], $args['database-login-password']);
            $test_connection = null;
        } catch (PDOException $e) {
            // Return with an error if something went wrong
            return $this->define_return(true, "There was an error connecting to the database with the following error:<br><strong>".$e->getMessage()."</strong>");
        }

        // Return true!
        return $this->define_return(false, true);
    }


    /**
     * Checks the values given by the user for the 'Customization and Security' step
     *
     * @param array $args
     * @return array
     */
    public function check_custom_security($args) {
        // Check for empty arguments
        if (empty($args)) {
            return false;
        }

        // Check for required arguments
        $required_args = array(
            "Site Name"     => "site-name",
            "Password Salt" => "password-salt",
            "Session Salt"  => "session-salt");
        $check_args = $this->check_args($required_args, $args);

        // Handle the result from the check arguments function
        if ($check_args['error'] == true) {
            return $check_args;
        }

        // Check the password salt length
        if (strlen($args['password-salt']) < 5) {
            return $this->define_return(true, "The <strong>Password Salt</strong> must be at least 5 characters long.");
        }

        // Check the session salt length
        if (strlen($args['session-salt']) < 5) {
            return $this->define_return(true, "The <strong>Session Salt</strong> must be at least 5 characters long.");
        }

        // Return the information
        return $this->define_return(false, json_encode($args));
    }


    /**
     * Checks the values given by the user for the 'First User Setup' step
     *
     * @param array $args
     * @return array
     */
    public function check_first_user($args) {
        // Check for empty arguments
        if (empty($args)) {
            return false;
        }

        // Check for required arguments
        $required_args = array(
            "Username"          => "username",
            "Password"          => "password",
            "Repeat Password"   => "repeat-password",
            "Email Address"     => "email",
            "First Name"        => "firstname",
            "Last Name"         => "lastname"
        );
        $check_args = $this->check_args($required_args, $args);

        // Handle the result from the check arguments function
        if ($check_args['error'] == true) {
            return $check_args;
        }

        // Validate the username length
        if (strlen($args['username']) < 4) {
            return $this->define_return(true, "The username must be at least 4 characters in length.");
        }

        // Validate the username characters
        if (preg_match("/[^a-zA-Z0-9.-_@\[\]:;]/", $args['username'])) {
            return $this->define_return(true, "The username contains invalid characters.");
        }

        // Validate the password length
        if (strlen($args['password']) < 4) {
            return $this->define_return(true, "The password must be at least 4 characters in length.");
        }

        // Check the password's match
        if ($args['password'] != $args['repeat-password']) {
            return $this->define_return(true, "The passwords provided do not match.");
        }

        // Validate the email address
        if (!filter_var($args['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->define_return(true, "The email provided is invalid.");
        }

        // Return the information
        return $this->define_return(false, json_encode($args));
    }

    /**
     * Checks the values given by the user for the 'Advanced Options' step
     *
     * @param array $args
     * @return array
     */
    public function check_advanced_options($args) {
        // Check for empty arguments
        if (empty($args)) {
            return false;
        }

        if ($args['configure-email'] == 1) {
            // Check for required arguments
            $required_args = array(
                "Email Host"        => "email-host",
                "Email Protocol"    => "email-protocol",
                "Email Port"        => "email-port",
                "Login Username"    => "email-login-username",
                "Login Password"    => "email-login-password"
            );
            $check_args = $this->check_args($required_args, $args);

            // Handle the result from the check arguments function
            if ($check_args['error'] == true) {
                return $check_args;
            }
        }

        return $this->define_return(false, json_encode($args));
    }


    /**
     * Creates a configuration file to house the Theamus information
     *
     * @param array $args
     * @return array
     */
    public function create_config_file($args) {
        // Don't run the function if there aren't any arguments
        if (empty($args)) {
            $this->restart_installation(); // clean slate for installer
            return false;
        }

        // Check the databas information
        $database_information = $this->check_database_configuration($args['database']);
        if ($database_information['error'] == true) {
            $this->restart_installation(); // clean slate for installer
            return $database_information;
        }

        // Define a path to the configuration file
        $file_path = path(ROOT."/config.php");

        // Check for an existing configuration file
        if (file_exists($file_path)) {
            if (!rename($file_path, path(ROOT."/config.backup-".date("d-m-Y -- h-ia").".php"))) {
                $last_error = error_get_last();
                $this->restart_installation(); // clean slate for installer
                return $this->define_return(true, "The old configuration file couldn't be renamed. - <strong>".$last_error['message']."</strong>");
            }
        }

        // Define the contents of the config file
        $config = "<?php\n\n";
        $config .= "\$config['Database']['Host Address'] = \"".urldecode($args['database']['database-host'])."\";\n";
        $config .= "\$config['Database']['Username'] = \"".urldecode($args['database']['database-login-username'])."\";\n";
        $config .= "\$config['Database']['Password'] = \"".urldecode($args['database']['database-login-password'])."\";\n";
        $config .= "\$config['Database']['Name'] = \"".urldecode($args['database']['database-name'])."\";\n\n";
        $config .= "\$config['timezone'] = \"America/Chicago\";\n\n";
        $config .= "\$config['salt']['password'] = \"".urldecode($args['security']['password_salt'])."\";\n";
        $config .= "\$config['salt']['session'] = \"".urldecode($args['security']['session_salt'])."\";\n\n";

        // Write a new configuration file
        $config_file = fopen($file_path, "w");

        // Check the config file opened ok and write the contents or error out
        if ($config_file) {
            fwrite($config_file, $config);
            fclose($config_file);
        } else {
            $last_error = error_get_last();
            $this->restart_installation(); // clean slate for installer
            return $this->define_return(true, "There was an error creating the configuration file. - <strong>".$last_error['message']."</strong>");
        }

        return $this->define_return(false, true);
    }


    /**
     * Creates the database structure for the Theamus platform.
     *
     * @param array $args
     * @return array
     */
    public function create_database_structure($args) {
        // Don't run the function if there aren't any arguments
        if (empty($args)) {
            $this->restart_installation(); // clean slate for installer
            return false;
        }

        // Define the structure queries
        $structure_queries = $this->define_queries($args['database']['database-table-prefix'], $this->sql_structure);

        // Connect to the database and gain access to the Theamus data class
        $tData = $this->database_connect();

        // Check the connection
        if ($tData == false) {
            $this->restart_installation(); // clean slate for installer
            return $this->define_return(true, "There was an error while trying to connect to the database.");
        }

        // Attempt to perform all of the queries
        $query = $tData->custom_query($structure_queries);

        // Check the query and return
        if ($query == false) {
            $this->restart_installation(); // clean slate for installer
            return $this->define_return(true, "There was an error creating the database structure.");
        }
        return $this->define_return(false, true);
    }


    /**
     * Adds the database data for the Theamus platform.
     *
     * @param array $args
     * @return array
     */
    public function add_database_data($args) {
        // Don't run the function if there aren't any arguments
        if (empty($args)) {
            $this->restart_installation(); // clean slate for installer
            return false;
        }

        // Define the structure queries
        $structure_queries = $this->define_queries($args['database']['database-table-prefix'], $this->sql_data);

        // Connect to the database and gain access to the Theamus data class
        $tData = $this->database_connect();

        // Check the connection
        if ($tData == false) {
            $this->restart_installation(); // clean slate for installer
            return $this->define_return(true, "There was an error while trying to connect to the database.");
        }

        // Attempt to perform all of the queries
        $query = $tData->custom_query($structure_queries);

        // Check the query and return
        if ($query == false) {
            $this->restart_installation(); // clean slate for installer
            return $this->define_return(true, "There was an error adding the database data.");
        }
        return $this->define_return(false, true);
    }


    /**
     * Creates the first Theamus user in the database
     *
     * @param array $args
     * @return array
     */
    public function create_first_user($args) {
        // Don't run the function if there aren't any arguments
        if (empty($args)) {
            $this->restart_installation(); // clean slate for installer
            return false;
        }

        // Check the first user information
        $first_user_information = $this->check_first_user($args['user']);
        if ($first_user_information['error'] == true) {
            $this->restart_installation(); // clean slate for installer
            return $first_user_information;
        }

        // Connect to the database and gain access to the Theamus data class
        $tData = $this->database_connect();

        // Define the secure password
        $salt = $tData->get_config_salt("password");
        $args['user']['password'] = hash('SHA256', $args['user']['password'].$salt);

        // Add the user to the database
        $query = $tData->insert_table_row(DB_PREFIX."users", array(
            "username"      => $args['user']['username'],
            "password"      => $args['user']['password'],
            "email"         => $args['user']['email'],
            "firstname"     => $args['user']['firstname'],
            "lastname"      => $args['user']['lastname'],
            "birthday"      => date("Y-m-d"),
            "admin"         => 1,
            "groups"        => "everyone,administrators",
            "permanent"     => 1,
            "picture"       => "default-user-picture.png",
            "created"       => date("Y-m-d H:i:s"),
            "active"        => 1
        ));

        // Check the query
        if ($query == false) {
            $this->restart_installation(); // clean slate for installer
            return $this->define_return(true, "There was an error creating the user in the database.");
        }
        return $this->define_return(false, true);
    }


    /**
     * Creates the system settings for Theamus in the database
     *
     * @param array $args
     * @return array
     */
    public function finish_installation($args) {
        // Don't run the function if there aren't any arguments
        if (empty($args)) {
            $this->restart_installation(); // clean slate for installer
            return false;
        }

        // Check the advanced options
        $advanced_options = $this->check_advanced_options($args['options']);
        if ($advanced_options['error'] == true) {
            $this->restart_installation(); // clean slate for installer
            return $advanced_options;
        }

        // Connect to the database and gain access to the Theamus data class
        $tData = $this->database_connect();

        // Check the connection
        if ($tData == false) {
            $this->restart_installation(); // clean slate for installer
            return $this->define_return(true, "There was an error while trying to connect to the database.");
        }


        // Add the system information to the database
        $query = $tData->insert_table_row(DB_PREFIX."settings", array(
            "prefix"            => DB_PREFIX,
            "name"              => $args['site_name'],
            "display_errors"    => $args['options']['developer-mode'],
            "developer_mode"    => $args['options']['developer-mode'],
            "email_host"        => $args['options']['email-host'],
            "email_protocol"    => $args['options']['email-protocol'],
            "email_port"        => $args['options']['email-port'],
            "email_user"        => $args['options']['email-login-username'],
            "email_password"    => $args['options']['email-login-password'],
            "installed"         => 1,
            "home"              => "{t:homepage;type=\"page\";id=\"1\";:}",
            "version"           => $this->version
        ));

        // Check the query
        if ($query == false) {
            $this->restart_installation(); // clean slate for installer
            return $this->define_return(true, "There was an error when installing the Theamus system information in the database.");
        }
        return $this->define_return(false, true);
    }
}