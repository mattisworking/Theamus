<?php

/**
 * DB - Theamus database access class
 * PHP Version 5.5.3
 * Version 1.4.1
 * @package Theamus
 * @link http://www.theamus.com/
 * @author MMT (helllomatt) <mmt@itsfake.com>
 */
class DB {
    /**
     * Contains the information given by the configuration file
     *
     * @var array $config
     */
    private $config;


    /**
     * The mysqli object for the class
     *
     * @var boolean|object $connection
     */
    public $connection = false;


    /**
     * The prefix to the system specific database tables
     *
     * @var string $prefix
     */
    public $prefix;


    /**
     * Holds the value of whether or not the PDO library is being used
     *
     * @var boolean $pdo
     */
    public $use_pdo = false;


    /**
     * Debugging tool to help see the query that is generated by any of the Theamus CRUD functions
     *
     * @var boolean $show_query
     */
    public $show_query = false;


    /**
     * Debugging tool to help see errors that occured during a query
     *
     * @var boolean $show_query_errors
     */
    public $show_query_errors = false;


    /**
     * Allows a developer to connect to a custom database
     *
     * @var array $connection_parameters
     */
    public $connection_parameters = array();


    /**
     * Tells the call class to try the installer if required
     *
     * @var boolean $try_installer
     */
    public $try_installer = false;


    /**
     * Array of all the query errors
     *
     * @var array $query_errors
     */
    public $query_errors = array();


    /**
     * Holds key/values for feature table prefixes to avoid excessive querying
     *
     * @var array $table_prefixes
     */
    private $table_prefixes = array();


    /**
     * Initializes the class, defines the configuration given by the system
     *
     * @return boolean
     */
    public function __construct($t = false) {
        // Only if this is a part of the Theamus construction
        if ($t != false) $this->Theamus = $t; // Make other Theamus classes usable

        // Set the timezone of the site
        $this->set_timezone();

        // Connect to the database if this is a part of the Theamus construction
        if ($t != false) {
            // Define the configuration information as defined by the site administrator
            $this->config = $this->define_system_configuration();

            // Connect to the database right here and now
            $db_connection = $this->connect(true);

            if (empty($this->config)) {
                $this->try_installer = true;
                return;
            }

            // Throw an exception to Theamus if the database wasn't connected to
            if (!$db_connection) throw new Exception('Failure connecting to the database.');

            // Define the system database table prefixes, too
            $this->system_prefix = $this->get_system_prefix();
        }

        return true;
    }


    /**
     * Defines the system configuration file into an array
     *
     * @return array $config
     */
    private function define_system_configuration() {
        // Define the path to the configuration file
        $config_path = $this->Theamus->file_path(ROOT."/config.php");

        $config = array(); // Define an empty array for the configuration information

        // Check if the configuration file exists and load it if it does
        if (file_exists($config_path)) require_once $config_path;
        return $config; // Return the configuration information
    }


    /**
     * Sets the timezone for the entire system
     *
     * @return
     */
    private function set_timezone() {
        // Check for a timezone in the site configuration information
        $tz = isset($this->config['timezone']) ? $this->config['timezone'] : "America/Chicago";

        // Set the timezone for the server
        date_default_timezone_set($tz);

        return;
    }


    /**
     * Gets the salt value from the sytem's configuration file
     *
     * @param string $type
     * @return string
     */
    public function get_config_salt($type) {
        $salt = $this->config['salt'][$type];
        return $salt;
    }


    /**
     * Connects the system to the database, you know, to do database things
     *
     * @return boolean
     */
    public function connect($pdo = false, $test = false) {
        // Check for a custom connection or not
        if (!empty($this->connection_parameters)) {
            if (!isset($this->connection_parameters['Host Address'])) return false;
            if (!isset($this->connection_parameters['Username'])) return false;
            if (!isset($this->connection_parameters['Password'])) return false;
            if (!isset($this->connection_parameters['Name'])) return false;

            // Define the custom connection information as the connection parameters
            $connection_parameters = $this->connection_parameters;

        // Check the configuration settings
        } elseif (isset($this->config['Database'])) $connection_parameters = $this->config['Database'];

        // Leave the configuration settings as the connection parameters
        else return false;

        // Check if the developer is looking to use PHP's mysqli as opposed to PDO
        if ($pdo == false) {
            // Connect to the database using the parameters defined above
            $connection = @new mysqli($connection_parameters['Host Address'],
                                      $connection_parameters['Username'],
                                      $connection_parameters['Password'],
                                      $connection_parameters['Name']);

            // Check the connection for errors
            if ($connection->connect_errno) return false;

            // Define the connection to be usable within this class and return it as well
            else {
                $this->connection = $connection;
                return $connection;
            }

        // If the developer is looking to use PDO
        } else {
            try {
                // Try to connect to the database
                $connection = @new PDO("mysql:host=".$connection_parameters['Host Address'].";dbname=".$connection_parameters['Name'].";",
                    $connection_parameters['Username'], $connection_parameters['Password']);

                // Define the connection to be usable within this class
                $this->connection = $connection;

                // Let it be known that PDO is what's being used here
                $this->use_pdo = true;

                // Return the connection
                return $connection;
            } catch (PDOException $ex) { return false; }
        }
    }


    /**
     * Gets the system's database prefixes
     *
     * e.g. "tm"
     *
     * @return string
     */
    public function get_system_prefix() {
        // Query the database for all of the tables
        $query = $this->custom_query('SHOW TABLES');

        // Define all of the tables in the database
        $tables = $this->fetch_rows($query, 'fetch_array', PDO::FETCH_COLUMN);

        // Loop through all of the tables
        foreach ($tables as $table) {
            // Explode the table name, looking for the prefix
            $table_array = explode('_', is_array($table) ? $table[0] : $table);

            // Continue on if there wasn't a prefix to look for
            if (count($table_array) == 0 || !isset($table_array[1])) continue;

            // Look for the 'settings' table for a default
            if ($table_array[1] == 'settings') return $table_array[0];
        }
    }

    /**
     * Defines the name of a system table
     *
     * @param string $name
     * @return string
     */
    public function system_table($name) {
        if (!$this->connection) return $name;
        return $this->system_prefix.'_'.$name;
    }


    /**
     * Takes a multi-demensional array and converts it into a single array
     *
     * @param array $array
     * @return array $ret
     */
    public function flatten_array($array) {
        if (!is_array($array)) return array($array);
        $ret = array();
        foreach ($array as $value) $ret = array_merge($ret, $this->flatten_array($value));
        return $ret;
    }


    /**
     * Decodes a Theamus specific encoding.
     * {t:<key>="<val>":} -> array("<key>"=>"<val>")s
     *
     * @param string $inp
     * @return array $ret
     * @throws Exception
     */
    public function t_decode($inp) {
        if ($inp == "") return array();

        preg_match_all('/{t:/i', $inp, $r);
        if (count($r[0]) > 1) throw new Exception("tData: Recusive encoding is not allowed.");

        preg_match("/{t:(.*?):}/i", $inp, $m);
        $exp = explode(";", $m[1]);
        foreach ($exp as $e) {
            if (strpos($e, "=") === false) $ret[] = $e;
            else {
                $iexp = explode("=", $e);
                $ret[$iexp[0]] = trim($iexp[1], "\"");
            }
        }

        return $ret;
    }


    /**
     * Takes an array of data and defines it to be used within a SQL query
     *
     * @param array $data
     * @return array
     */
    private function define_query_data($data) {
        // Define the return array with a random number key/value
        $return = array("random_number" => rand(0, 999999999));

        // Loop through the given data to define the desired variables
        foreach ($data as $key => $value) {
            // If the value is an array recurse into it, defining it as a part of the return data
            if (is_array($value)) {
                $return['data'][] = $this->define_query_data($value);

            // If the value is not an array, define the desired return variables
            } else {
                // If MySQLi is being used
                if ($this->use_pdo == false) {
                    $return['columns'][] = "`$key`";
                    if (strpos(strtolower($value), '[func]') !== false && !is_numeric($value)) {
                        $return['prepare_keys'] = $this->connection->real_escape_string(str_replace("[func]", "", $value));
                    } else {
                        $return['prepare_keys'][] = "'".$this->connection->real_escape_string($value)."'";
                    }
                    $return['prepare_values'] = array();

                // If PDO is being used
                } else {
                    $random_key = ":".$key.$return['random_number'];            // random_key for shorter code
                    $return['columns'][] = "`$key`";
                    if (strpos(strtolower($value), '[func]') !== false && !is_numeric($value)) {
                        $return['prepare_keys'][] = str_replace("[func]", "", $value);
                    } else {
                        $return['prepare_keys'][]               = $random_key;
                        $return['prepare_values'][$random_key]  = $value;
                    }
                }
            }
        }

        // Return the desired data
        return $return;
    }


    /**
     * Takes an array of data and recursively turns it into a beautiful clause statement
     *
     * @param array $clause
     * @return string
     */
    private function define_clause($clause) {
        $return_clause = $return_inner = array(); // Defaults

        if (!isset($clause['conditions']) || !isset($clause['operator'])) {
            $this->clause_values = array();
            return "";
        }

        // Loop through all of the conditions
        foreach ($clause['conditions'] as $key => $value) {
            // If this condition is guessed to be another condition, recurse into it
            if (is_numeric($key)) {
                $return_clause[] = $this->define_clause($value);

            // If this is a condition value
            } else {
                // Define the key and the operator
                $first_character = substr($key, 0, 1);

                $equals = "=";
                $key = "`".$key."`";

                if ($first_character == "[") {
                    preg_match("/\[(.*?)\]/", $key, $matches);
                    for ($i = 0; $i < strlen($matches[1]); $i++) {
                        switch ($matches[1][$i]) {
                            case "!":
                                if (isset($matches[1][$i + 1]) && $matches[1][$i + 1] == '%') {
                                    $key = str_replace("!%", "", $key);
                                    $equals = "NOT LIKE";
                                    break 2;
                                } else {
                                    $key = str_replace("!", "", $key);
                                    $equals = "!=";
                                    break;
                                }
                            case "%":
                                $equals = "LIKE";
                                $key = str_replace("%", "", $key);
                                break;
                            case "`":
                                $key = str_replace("`", "", $key);
                                $equals = "=";
                                break;
                            case "<":
                                if (isset($matches[1][$i + 1]) && $matches[1][$i + 1] == '=') {
                                    $key = str_replace("<=", "", $key);
                                    $equals = "<=";
                                    break 2;
                                } else {
                                    $key = str_replace("<", "", $key);
                                    $equals = "<";
                                    break;
                                }
                            case ">":
                                if (isset($matches[1][$i + 1]) && $matches[1][$i + 1] == '=') {
                                    $key = str_replace(">=", "", $key);
                                    $equals = ">=";
                                    break 2;
                                } else {
                                    $key = str_replace(">", "", $key);
                                    $equals = ">";
                                    break;
                                }
                        }
                    }
                    $key = str_replace("[", "", str_replace("]", "", $key));
                }

                // If using MySQLi
                if ($this->use_pdo == false) {
                    if (strpos(strtolower($value), '[func]') !== false && !is_numeric($value)) {
                        $return_inner[] = "{$key} {$equals} ".str_replace("[func]", "", $value);
                    } else {
                        // Define an escaped key/value combination and define the clause values as blank
                        $return_inner[] = "$key $equals '".$this->connection->real_escape_string($value)."'";
                        $this->clause_values = array();
                    }

                // If using PDO
                } else {
                    if (strpos(strtolower($value), '[func]') !== false && !is_numeric($value)) {
                        $return_inner[] = "{$key} {$equals} ".str_replace("[func]", "", $value);
                    } else {
                        // Define a random key, then the key/value combination, then add the value to the clause values array
                        $random_key = ":".substr(md5($key.rand(0,999999999)), 0, 15);
                        $return_inner[] = "$key $equals $random_key";
                        $this->clause_values[$random_key] = $value;
                    }
                }
            }
        }

        // If returning values (children)
        if (!empty($return_inner)) {
            return "(".implode(" ".$clause['operator']." ", $return_inner).")";

        // If returning a complete child
        } else {
            return "(".implode(" ".$clause['operator']." ", $return_clause).")";
        }

        // Return the entire thing
        return $return_clause;
    }


    /**
     * Returns the amount of rows that exist in a database object
     *
     * @param object $object
     * @return int
     */
    public function count_rows($object) {
        if ($this->use_pdo == false) {
            return $object->num_rows;
        }
        return $object->rowCount();
    }


    /**
     * Returns query errors
     *
     * @param boolean $all
     * @return string
     */
    public function get_last_error($all = false) {
        // Return nothing if there arent any query errors
        if (empty($this->query_errors)) return 'No query errors were found.';

        // Return the last array item error
        elseif (!$all) return $this->query_errors[count($this->query_errors) - 1];

        // Return all of the errors
        else return $this->query_errors;
    }


    /**
     * Gathers provided data and generates a SQL query to perform based on that data.
     *
     * @param string $table_name
     * @param array $data
     * @return boolean
     */
    public function insert_table_row($table_name = "", $data = array()) {
        if ($this->connection == false) {
            return false;
        }

        // Check the table_name and data variables for values
        if ($table_name == "" || empty($data)) {
            return false;
        }

        // Define the SQL statement information
        $sql = $this->define_query_data($data);

        // If a multi-query is going to happen
        if (isset($sql['data'])) {
            // Define the variables that will be used to create the SQL query
            $sql['prepare_values']  = array();
            $sql['statement']       = "";

            // Loop through the given data, creating one large multi-query
            foreach ($sql['data'] as $sql_data) {
                $sql_values[] = "(".implode(", ", $sql_data['prepare_keys']).")";
                $sql['prepare_values']   = array_merge($sql_data['prepare_values'], $sql['prepare_values']);
            }
            $sql['statement'] = "INSERT INTO `$table_name` (".implode(", ", $sql_data['columns']).") VALUES ".implode(", ", $sql_values).";";

            // Clean up the SQL data
            unset($sql['random_number'], $sql['data']);

        // If it's not a multi-query then define the statement
        } else {
            $sql['statement'] = "INSERT INTO `$table_name` (".implode(", ", $sql['columns']).") VALUES (".implode(", ", $sql['prepare_keys']).");";

            // Clean up the SQL data
            unset($sql['random_number'], $sql['columns'], $sql['prepare_keys']);
        }

        // Show the query if desired
        if ($this->show_query == true) {
            $this->Theamus->pre(array("query" => $sql['statement'], "variables" => $sql['prepare_values']));
        }

        // If a connection exists
        if ($this->connection) {
            // If using MySQLi - run the query
            if ($this->use_pdo == false) {
                $query = $this->connection->query($sql['statement']);
                if ($query) {
                    return $query;
                } else {
                    $this->query_errors[] = $this->connection->error;

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$this->connection->errno." - Message: ".$this->connection->error);
                    }
                    return false;
                }

            // If using PDO - prepare the statement and execute
            } else {
                $query = $this->connection->prepare($sql['statement']);
                if ($query->execute($sql['prepare_values'])) {
                    return $query;
                } else {
                    $query_error = $query->errorInfo();
                    $this->query_errors[] = $query_error[2];

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$query_error[1]." - Message: ".$query_error[2]);
                    }
                    return false;
                }
            }
        }

        $this->query_errors[] = 'No database connection exists.';
        return false;
    }


    /**
     * Updates a table row based on the information given
     *
     * @param string $table_name
     * @param array $data
     * @param array $clause
     * @return boolean
     */
    public function update_table_row($table_name = "", $data = array(), $clause = array()) {
        if ($this->connection == false) {
            return false;
        }

        // Check the table_name and data variables for values
        if ($table_name == "" || empty($data)) {
            return false;
        }

        // Define the query data
        $sql = $this->define_query_data($data);

        $sql    = isset($sql['data']) ? $sql : array("data" => array($sql));
        $clause = isset($clause[0]) ? $clause : array($clause);
        if (isset($sql['data'])) {

            if (count($sql['data']) != count($clause)) {
                return false;
            }

            for ($s = 0; $s < count($sql['data']); $s++) {
                $data = $sql['data'][$s];
                $temp_clause = isset($clause[$s]) ? $clause[$s] : array();

                // Let the parameters start fresh
                $this->clause_values = array();
                $set = array();

                // If using MySQLi
                if ($this->use_pdo == false) {
                    // Loop through the values, defining them to be used in the statement
                    for ($i = 0; $i < count($data['columns']); $i++) {
                        $set[] = $data['columns'][$i]." = ".$data['prepare_keys'][$i];
                    }

                // If using PDO
                } else {
                    // Loop through the values, defining them to be used in the statement
                    foreach (array_keys($data['prepare_values']) as $key) {
                        $set[] = "`".trim(trim($key, ":"), $data['random_number'])."` = ".$key;
                    }
                }

                // If there is a WHERE
                if (!empty($temp_clause)) {
                    // Define the WHERE statement values, merge the clause values with the set items values then define the statment
                    $where = $this->define_clause($temp_clause);
                    $temp_clause = $where != "" ? " WHERE $where" : "";
                    $temp_prepare_values[] = array_merge($this->clause_values, $data['prepare_values']);
                    $temp_queries[] = "UPDATE `$table_name` SET ".implode(", ", $set).$temp_clause.";";

                // No WHERE - define a statement for all!
                } else {
                    $temp_prepare_values[] = $data['prepare_values'];
                    $temp_queries[] = "UPDATE `$table_name` SET ".implode(", ", $set).";";
                }
            }

            // Flatten the prepared values
            $sql['prepare_values'] = array();
            foreach ($temp_prepare_values as $prepare_value) {
                $sql['prepare_values'] = array_merge($prepare_value, $sql['prepare_values']);
            }

            // Flatten the queries
            $sql['statement'] = implode(" ", $temp_queries);
        }

        // Show the query if desired
        if ($this->show_query == true) {
            $this->Theamus->pre(array("query" => $sql['statement'], "variables" => $sql['prepare_values']));
        }


        // If a connection exists
        if ($this->connection) {
            // If using MySQLi - run the query
            if ($this->use_pdo == false) {
                $return = $this->connection->multi_query($sql['statement']) ? true : false;
                if ($return) {
                    while ($this->connection->next_result()) continue;
                    return $return;
                } else {
                    $this->query_errors[] = $this->connection->error;

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$this->connection->errno." - Message: ".$this->connection->error);
                    }
                    return false;
                }

            // If using PDO - prepare the statement and execute
            } else {
                $query = $this->connection->prepare($sql['statement']);
                if ($query->execute($sql['prepare_values'])) {
                    return $query;
                } else {
                    $query_error = $query->errorInfo();
                    $this->query_errors[] = $query_error[2];

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$query_error[1]." - Message: ".$query_error[2]);
                    }
                    return false;
                }
            }
        }

        $this->query_errors[] = 'No database connection exists.';
        return false;
    }


    /**
     * Deletes a row from a database table
     *
     * @param string $table_name
     * @param string $clause
     * @return boolean
     */
    public function delete_table_row($table_name = "", $clause = array()) {
        if ($this->connection == false) {
            return false;
        }

        // Let the parameters start fresh
        $this->clause_values = array();

        // Check the table_name and data variables for values
        if ($table_name == "") {
            return false;
        }

        $temp_statement = $temp_values = array();

        // If there is a WHERE
        if (!empty($clause)) {
            $clauses = isset($clause[0]) ? $clause : array($clause);
            foreach ($clauses as $clause) {
                // Define the WHERE statement values, define the prepare values and the statement
                $where = $this->define_clause($clause);
                $clause = $where != "" ? " WHERE $where" : "";

                $temp_statement[] = "DELETE FROM `$table_name`$clause;";
                $temp_prepare_values[] = $this->clause_values;
            }
        // No WHERE - define a statement for all
        } else {
            $temp_statement[] = "DELETE FROM `$table_name`;";
            $temp_prepare_values[] = array();
        }

        // Flatten the prepared values
        $sql['prepare_values'] = array();
        foreach ($temp_prepare_values as $prepare_value) {
            $sql['prepare_values'] = array_merge($prepare_value, $sql['prepare_values']);
        }

        $sql['statement'] = implode(" ", $temp_statement);

        // Show the query if desired
        if ($this->show_query == true) {
            $this->Theamus->pre(array("query" => $sql['statement'], "variables" => $sql['prepare_values']));
        }

        // If a connection exists
        if ($this->connection) {
            // If using MySQLi - run the query
            if ($this->use_pdo == false) {
                $query = $this->connection->query($sql['statement']);
                if ($query) {
                    return $query;
                } else {
                    $this->query_errors[] = $this->connection->error;

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$this->connection->errno." - Message: ".$this->connection->error);
                    }
                    return false;
                }

            // If using PDO - prepare the statement and execute
            } else {
                $query = $this->connection->prepare($sql['statement']);
                if ($query->execute($sql['prepare_values'])) {
                    return $query;
                } else {
                    $query_error = $query->errorInfo();
                    $this->query_errors[] = $query_error[2];

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$query_error[1]." - Message: ".$query_error[2]);
                    }
                    return false;
                }
            }
        }

        $this->query_errors[] = 'No database connection exists.';
        return false;
    }


    /**
     * Selects rows from a table and returns the object holding them, or returns false if there is an error
     *
     * @param string $table_name
     * @param array $columns
     * @param array $clause
     * @return boolean|object
     */
    public function select_from_table($table_name = "", $columns = array(), $clause = array(), $extras = "") {
        if ($this->connection == false) {
            if ($this->show_query_errors == true) {
                $this->Theamus->pre("Cannot perform query because there is no connection available.");
            }
            return false;
        }

        // Let the parameters start fresh
        $this->clause_values = array();

        // Check the table_name and data variables for values
        if ($table_name == "") {
            if ($this->show_query_errors == true) {
                $this->Theamus->pre("Query was unsuccessful because there was no table name defined.");
            }
            return false;
        }

        // Define the columns
        if (empty($columns)) {
            $columns = "*";
        } else {
            foreach ($columns as $column) {
                $prepared_columns[] = "`$column`";
            }
            $columns = implode(", ", $prepared_columns);
        }

        // Define the extras
        if ($extras != "") {
            $extras = " $extras";
        }

        // If there is a WHERE
        if (!empty($clause)) {
            // Define the WHERE statement values, define the prepare values and the statement
            $where = $this->define_clause($clause);
            $clause = $where != "" ? " WHERE $where" : "";

            $sql['statement'] = "SELECT $columns FROM `$table_name`$clause"."$extras;";
            $sql['prepare_values'] = $this->clause_values;

        // No WHERE - define a statement for all
        } else {
            $sql['statement'] = "SELECT $columns FROM `$table_name`"."$extras;";
            $sql['prepare_values'] = array();
        }

        // Show the query if desired
        if ($this->show_query == true) {
            $this->Theamus->pre(array("query" => $sql['statement'], "variables" => $sql['prepare_values']));
        }

        // If a connection exists
        if ($this->connection) {
            // If using MySQLi - run the query
            if ($this->use_pdo == false) {
                $query = $this->connection->query($sql['statement']);
                if ($query) {
                    return $query;
                } else {
                    $this->query_errors[] = $this->connection->error;

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$this->connection->errno." - Message: ".$this->connection->error);
                    }
                    return false;
                }

            // If using PDO - prepare the statement and execute
            } else {
                $query = $this->connection->prepare($sql['statement']);
                if ($query->execute($sql['prepare_values'])) {
                    return $query;
                } else {
                    $query_error = $query->errorInfo();
                    $this->query_errors[] = $query_error[2];

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$query_error[1]." - Message: ".$query_error[2]);
                    }
                    return false;
                }
            }
        }
        if ($this->show_query_errors == true) {
            $this->Theamus->pre("Nothing worked for this query.  The function ran but nothing happened.");
        }

        $this->query_errors[] = 'No database connection exists.';
        return false;
    }


    /**
     * Fetches rows from a SQL query object and returns them as an array of data
     *
     * @param object $object
     * @param string $mysqli_fetch
     * @param long $pdo_fetch
     * @return array
     */
    public function fetch_rows($object, $mysqli_fetch = "fetch_assoc", $pdo_fetch = PDO::FETCH_ASSOC) {
        if (!$object) return array(); // Return empty array if there's no valid query

        // If using MySQLi
        if ($this->use_pdo == false) {
            // If there are multiple rows to return
            if ($object->num_rows > 0) {
                // Loop through all of the rows, adding them to a return catch
                while ($row = $object->$mysqli_fetch()) {
                    $return[] = $row;
                }

                // Return the data as it should be depending on the amount of results
                return count($return) > 1 ? $return : $return[0];
            }

            // Return the whole thing as is
            return $object->$mysqli_fetch();
        } else {
            // If there are multiple rows to return
            if ($object->rowCount() > 1) {
                // Return the whole thing as is
                return $object->fetchAll($pdo_fetch);
            } else {
                // Define all of the information into an array
                $row = $object->fetchAll($pdo_fetch);

                // Return the data as it should be depending on the amount of results
                return isset($row[0]) ? $row[0] : $row;
            }
        }
    }


    /**
     * Takes a given string (query statement) and runs it with sanitized, safe variables
     *
     * @param string $query
     * @param array $variables
     * @return boolean
     */
    public function custom_query($query, $variables = array()) {
        // Show the query if desired
        if ($this->show_query == true) {
            $this->Theamus->pre(array("query" => $query, "variables" => $variables));
        }

        // If a connection exists
        if ($this->connection) {
            // If using MySQLi - run the query
            if ($this->use_pdo == false) {
                foreach($variables as $key => $variable) {
                    $query = str_replace($key, "'".$this->connection->real_escape_string($variable)."'", $query);
                }

                $sql_query = $this->connection->query($query);
                if ($sql_query) {
                    return $sql_query;
                } else {
                    $this->query_errors[] = $this->connection->error;

                    if ($this->show_query_errors == true) {
                        $this->Theamus->pre("The query failed to execute. Code: ".$this->connection->errno." - Message: ".$this->connection->error);
                    }
                    return false;
                }

            // If using PDO - prepare the statement and execute
            } else {
                $query = $this->connection->prepare($query);
                if ($query->execute($variables)) {
                    return $query;
                } else {
                    $query_error = $query->errorInfo();
                    $this->query_errors[] = $query_error[2];
                }
                return false;
            }
        }
        return false;
    }


    /**
     * Gets the table prefix for a feature from the database and defines the complete
     * table name with the prefix attached
     *
     * @param string $name
     * @param feature $feature
     * @return string
     * @throws Exception
     */
    public function table($name= '', $feature = '') {
        // Define the feature to look for the prefix with
        $feature_alias = $feature != '' ? $feature : $this->Theamus->Call->feature['config']['folder_name'];

        // Check for an existing table prefix value for this feature and return it before
        // querying the database for the prefix again
        if (isset($this->table_prefixes[$feature_alias])) {
            return $this->table_prefixes[$feature_alias].$name;
        }

        // Query the database looking for the db_prefix
        $query = $this->select_from_table(
            $this->system_table('features'),
            array('db_prefix'),
            array('operator' => '',
                'conditions' => array('alias' => $feature_alias)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->get_last_error()); // Log the query error
            throw new Exception('Failed to get table name.');
        }

        // Check the query for results
        if ($this->count_rows($query) == 0) throw new Exception('Could not find the table name.');

        // Define the query information
        $results = $this->fetch_rows($query);

        // Add the table prefix to the class variable to avoid redundant queries
        $this->table_prefixes[$feature_alias] = substr($results['db_prefix'], -1) == '_' ? $results['db_prefix'] : "{$results['db_prefix']}_";

        // Return the completed table name
        return $this->table_prefixes[$feature_alias].$name;
    }
}
