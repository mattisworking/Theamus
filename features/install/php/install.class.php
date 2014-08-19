<?php

class Install {
    private $sql_structure  = "structure.sql";
    private $sql_data       = "data.sql";


    /**
     * Connects to Theamus
     *
     * @param object $t
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;
        return;
    }


    /**
     * Checks a required array of keys/values against the provided aruments for existence and value
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    private function check_args($required_args, $args) {
        foreach ($required_args as $key => $value) {
            // Check that the argument was submitted with the form
            if (!isset($args[$value])) {
                throw new Exception("The field <strong>$key</strong> is missing from the recieved arguments.");
            }

            // Check that the argument has a value
            if ($args[$value] == "") {
                throw new Exception("The field <strong>$key</strong> is required and cannot be blank.");
            }
        }

        return true;
    }


    /**
     * Defines the queries to perform that will build the structure of the Theamus database
     *
     * @return string
     */
    private function define_queries($table_prefix, $for) {
        // Define the sql structure file path
        $file_path = $this->Theamus->file_path(ROOT."/features/install/sql/".$for);

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
                if (strpos($table_name, "tm_") !== false){
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
        // Check the connection
        if ($this->Theamus->DB->connection != false) {
            // Get all of the tables from the database
            $table_query = $this->Theamus->DB->custom_query("SHOW TABLES");

            // Check the query and define the tables
            if ($table_query != false) {
                $results    = $this->Theamus->DB->fetch_rows($table_query, "", PDO::FETCH_NUM);
                $tables     = isset($results[0]) ? $results : array($results);

                $drop_queries = array();

                // Loop through the tables, defining their drop queries
                foreach ($tables as $table) {
                    $drop_queries[] = "DROP TABLE `$table[0]`;";
                }

                // Perform the drop queries, if there are any
                if (!empty($drop_queries)) {
                    $this->Theamus->DB->custom_query(implode(" ", $drop_queries));
                }
            }
        }

        // Delete the configuration file
        $config_file = $this->Theamus->file_path(ROOT."/config.php");
        if (file_exists($config_file)) {
            unlink($config_file);
        }

        return;
    }


    /**
     * Validates information given by a form for the database configuration settings
     *
     * @param array $args
     * @return array
     */
    public function check_database_configuration($args) {
        // Check for empty arguments
        if (empty($args)) throw new Exception('Failed to find the database information.');

        // Check for required arguments
        $required_args = array(
            "Database Host"     => "database_host",
            "Login Username"    => "database_username",
            "Login Password"    => "database_password",
            "Database Name"     => "database_name",
            "Table Prefix"      => "database_prefix");
        $this->check_args($required_args, $args);

        // Check the table prefix
        $table_prefix = $args['database_prefix'];

        // Table prefix length
        if (strlen(trim($table_prefix, "_")) > 7 || strlen(trim($table_prefix, "_")) < 2) {
            throw new Exception("The table prefix must be between 2 and 7 characters, not including the trailing underscore.");
        }

        // Table prefix underscores
        if (preg_match("/[^A-Za-z0-9]/i", trim($table_prefix, "_"))) {
            throw new Exception("The table prefix must be alphanumeric, not including the trailing underscore.");
        }

        // Trailing underscore
        if (substr($table_prefix, -1) != "_") {
            $args['database_prefix'] = $table_prefix."";
        }

        // Return the information
        return true;
    }


    /**
     * Attempts to connect to a database using PHP PDO MySQL with the given information from before
     *
     * @param array $args
     * @return array
     */
    public function check_database_connection($args) {
        // Try to connect to the database
        try {
            // Connect/disconnect
            $test_connection = new PDO("mysql:host=".$args['database_host'].";dbname=".$args['database_name'], $args['database_username'], $args['database_password']);
            $test_connection = null;
        } catch (PDOException $e) {
            // Return with an error if something went wrong
            throw new Exception("There was an error connecting to the database with the following error:<br><strong>".$e->getMessage()."</strong>");
        }

        return true; // Return true!
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
            "Username"          => "user_username",
            "Password"          => "user_password",
            "Email Address"     => "user_email",
            "First Name"        => "user_firstname",
            "Last Name"         => "user_lastname"
        );
        $this->check_args($required_args, $args);

        // Validate the username length
        if (strlen($args['user_username']) < 4) {
            throw new Exception("The username must be at least 4 characters in length.");
        }

        // Validate the username characters
        if (preg_match("/[^a-zA-Z0-9.-_@\[\]:;]/", $args['user_username'])) {
            throw new Exception("The username contains invalid characters.");
        }

        // Validate the password length
        if (strlen($args['user_password']) < 4) {
            throw new Exception("The password must be at least 4 characters in length.");
        }

        // Validate the email address
        if (!filter_var($args['user_email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("The email provided is invalid.");
        }

        return true;
    }


    /**
     * Creates a configuration file to house the Theamus information
     *
     * @param array $args
     * @return array
     */
    public function create_config_file($args) {
        $args['security_password-salt'] = $args['security_password-salt'] == '' ? md5(time().rand(0, 99999999)) : $args['security_password-salt'];
        $args['security_session-salt'] = $args['security_session-salt'] == '' ? md5(time().rand(0, 99999999)) : $args['security_session-salt'];

        // Check the password salt length
        if (strlen($args['security_password-salt']) < 5) {
            throw new Exception("The <strong>Password Salt</strong> must be at least 5 characters long.");
        }

        // Check the session salt length
        if (strlen($args['security_session-salt']) < 5) {
            throw new Exception("The <strong>Session Salt</strong> must be at least 5 characters long.");
        }

        // Define a path to the configuration file
        $file_path = $this->Theamus->file_path(ROOT."/config.php");

        // Check for an existing configuration file
        if (file_exists($file_path)) {
            if (!rename($file_path, $this->Theamus->file_path(ROOT."/config.backup-".date("d-m-Y -- h-ia").".php"))) {
                $last_error = error_get_last();
                $this->restart_installation();
                throw new Exception("The old configuration file couldn't be renamed. - <strong>".$last_error['message']."</strong>");
            }
        }

        // Define the contents of the config file
        $config = "<?php\n\n";
        $config .= "\$config['Database']['Host Address'] = \"".urldecode($args['database_host'])."\";\n";
        $config .= "\$config['Database']['Username'] = \"".urldecode($args['database_username'])."\";\n";
        $config .= "\$config['Database']['Password'] = \"".urldecode($args['database_password'])."\";\n";
        $config .= "\$config['Database']['Name'] = \"".urldecode($args['database_name'])."\";\n\n";
        $config .= "\$config['timezone'] = \"America/Chicago\";\n\n";
        $config .= "\$config['salt']['password'] = \"".urldecode($args['security_password-salt'])."\";\n";
        $config .= "\$config['salt']['session'] = \"".urldecode($args['security_session-salt'])."\";\n\n";

        // Write a new configuration file
        $config_file = fopen($file_path, "w");

        // Check the config file opened ok and write the contents or error out
        if ($config_file) {
            fwrite($config_file, $config);
            fclose($config_file);
        } else {
            $last_error = error_get_last();
            $this->restart_installation();
            throw new Exception("There was an error creating the configuration file. - <strong>".$last_error['message']."</strong>");
        }

        return true;
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
            $this->restart_installation();
            return false;
        }

        // Define the structure queries
        $structure_queries = $this->define_queries($args['database_prefix'], $this->sql_structure);

        // Check the connection
        if ($this->Theamus->DB->connection == false) {
            $this->restart_installation();
            throw new Exception("There was an error while trying to connect to the database.");
        }

        // Attempt to perform all of the queries
        $query = $this->Theamus->DB->custom_query($structure_queries);

        // Check the query and return
        if ($query == false) {
            $this->restart_installation();
            throw new Exception("There was an error creating the database structure.");
        }

        return true;
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
            $this->restart_installation();
            return false;
        }

        // Define the structure queries
        $structure_queries = $this->define_queries($args['database_prefix'], $this->sql_data);

        // Check the connection
        if ($this->Theamus->DB->connection == false) {
            $this->restart_installation();
            throw new Exception("There was an error while trying to connect to the database.");
        }

        // Attempt to perform all of the queries
        $query = $this->Theamus->DB->custom_query($structure_queries);

        // Check the query and return
        if ($query == false) {
            $this->restart_installation();
            throw new Exception("There was an error adding the database data.");
        }
        return true;
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
            $this->restart_installation();
            return false;
        }

        // Define the connection parameters
        $this->Theamus->DB->connection_parameters = array(
            'Host Address' => $args['database_host'],
            'Username'  => $args['database_username'],
            'Password'  => $args['database_password'],
            'Name'      => $args['database_name']
        );

        // Connect to the database
        $this->Theamus->DB->connect(true);

        // Define the secure password
        $salt = $args['security_password-salt'];
        $args['user_password'] = hash('SHA256', $args['user_password'].$salt);

        // Define the table prefix
        $table_prefix = substr($args['database_prefix'], -1) != "_" ? $args['database_prefix'].'_' : $args['database_prefix'];

        // Add the user to the database
        $query = $this->Theamus->DB->insert_table_row(
            $table_prefix."users",
            array("username"    => $args['user_username'],
                "password"      => $args['user_password'],
                "email"         => $args['user_email'],
                "firstname"     => $args['user_firstname'],
                "lastname"      => $args['user_lastname'],
                "birthday"      => 'now()',
                "admin"         => 1,
                "groups"        => "everyone,administrators",
                "permanent"     => 1,
                "picture"       => "default-user-picture.png",
                "created"       => 'now()',
                "active"        => 1));

        // Check the query
        if (!$query) {
            $this->restart_installation();
            throw new Exception("There was an error creating the user in the database.");
        }

        return true;
    }


    public function install_settings($args) {
        if ($this->Theamus->DB->connection == false) return false;

        $table_prefix = substr($args['database_prefix'], -1) != "_" ? $args['database_prefix'].'_' : $args['database_prefix'];

        // Add the system information to the database
        $query = $this->Theamus->DB->insert_table_row(
            $table_prefix.'settings',
            array("prefix"          => $table_prefix,
                "name"              => $args['site_name'],
                "display_errors"    => ($args['developer-mode'] === true ? 1 : 0),
                "developer_mode"    => ($args['developer-mode'] === true ? 1 : 0),
                "email_host"        => $args['email-host'],
                "email_protocol"    => $args['email-protocol'],
                "email_port"        => $args['email-port'],
                "email_user"        => $args['email-login-username'],
                "email_password"    => $args['email-login-password'],
                "installed"         => 0,
                "home"              => "{t:homepage;type=\"page\";id=\"1\";:}",
                "version"           => $this->Theamus->version));

        // Check the query
        if (!$query) {
            $this->restart_installation();
            throw new Exception("There was an error when installing the Theamus system information in the database.".$this->Theamus->DB->get_last_error());
        }

        return true;
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
            $this->restart_installation();
            return false;
        }

        // Define the connection parameters
        $this->Theamus->DB->connection_parameters = array(
            'Host Address' => $args['database_host'],
            'Username'  => $args['database_username'],
            'Password'  => $args['database_password'],
            'Name'      => $args['database_name']
        );

        // Connect to the database
        $this->Theamus->DB->connect(true);

        // Define the table prefix to be right
        $table_prefix = substr($args['database_prefix'], -1) != "_" ? $args['database_prefix'].'_' : $args['database_prefix'];

        // Update the settings table to reflect a successful installation
        $query = $this->Theamus->DB->update_table_row(
            $table_prefix."settings",
            array("installed" => 1));

        // Check the query
        if ($query == false) {
            $this->restart_installation();
            throw new Exception("There was an error when installing the Theamus system information in the database.");
        }

        // Create the configuration file
        $this->create_config_file($args);

        return true;
    }


    /**
     * Checks the incoming form for all the proper information
     *
     * @param array $args
     * @return boolean
     */
    public function check_form($args) {
        // Check the database information
        $this->check_database_configuration($args);
        $this->check_database_connection($args);

        // Check the user information
        $this->check_first_user($args);

        return true;
    }


    /**
     * Check for a valid site name
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    protected function check_site_name($args) {
        if (!isset($args['site_name']) || $args['site_name'] == '') throw new Exception('Please fill out the "Site Name" field.');
        return true;
    }


    /**
     * Looks in the database given for a 'settings' table to see if Theamus
     *  has already been installed
     *
     * @return
     * @throws Exception
     */
    protected function test_database_structure() {
        // Test for an actual connection to the database first
        if (!$this->Theamus->DB->connection) {
            $this->restart_installation();
            throw new Exception('Failed to connect to the database.');
        }

        // Query for al tables
        $table_query = $this->Theamus->DB->custom_query("SHOW TABLES");

        // Check the query for errors
        if (!$table_query) {
            $this->restart_installation();
            throw new Exception('Failed to test for an existing installation.');
        }

        // If there are no tables, stop here
        if ($this->Theamus->DB->count_rows($table_query) == 0) return;

        // Define the tables
        $results = $this->Theamus->DB->fetch_rows($table_query, "", PDO::FETCH_NUM);
        $tables  = isset($results[0]) ? $results : array($results);

        // Loop through all of the tables
        foreach ($tables as $table) {
            // Look for a 'settings' table
            if (strpos($table, 'settings') !== false) {
                throw new Exception('Theamus has already been installed in this database.');
            }
        }

        return;
    }


    /**
     * Installs the database structure, data and settings for Theamus
     *
     * @param array $args
     * @return boolean
     */
    public function install_database_config($args) {
        // Validate the site name
        $this->check_site_name($args);

        // Define the connection parameters
        $this->Theamus->DB->connection_parameters = array(
            'Host Address' => $args['database_host'],
            'Username'  => $args['database_username'],
            'Password'  => $args['database_password'],
            'Name'      => $args['database_name']
        );

        // Attempt to connect to the database
        $this->Theamus->DB->connect(true);

        // Look for an existing installation
        $this->test_database_structure();

        // Install the structure, data and settings
        $this->create_database_structure($args);
        $this->add_database_data($args);
        $this->install_settings($args);

        return true;
    }
}