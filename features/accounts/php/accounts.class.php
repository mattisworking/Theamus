<?php

class Accounts {
    protected $tData;
    protected $tUser;

    public function __construct() {
        $this->initialize_variables();
    }

    public function __destruct() {
        $this->tData->disconnect();
    }

    private function initialize_variables() {
        $this->tData            = new tData();
        $this->tData->db        = $this->tData->connect(true);
        $this->tData->prefix    = $this->tData->get_system_prefix();
        $this->tUser            = new tUser();
    }

    /**
     * Checks a variable to see if it is a valid username
     *
     * @param string $username
     * @return string
     */
    protected function define_username($username) {
        // Check for illegal characters
        if (preg_match("/[^a-zA-Z0-9.-_@\[\]:;]/", $username)) {
            return "invalid";

        // Check the username length
        } elseif (strlen($username) > 25 || strlen($username) < 4) {
            return "invalid";

        // Check for and existing username
        } elseif ($this->check_unused_username($username) == false) {
            return "taken";
        } else {
            return true;
        }
    }


    /**
     * Checks the database for any existing usernames
     *
     * @param string $username
     * @return boolean
     */
    protected function check_unused_username($username) {
        // Make the string sql safe and query the database for the username
        $query = $this->tData->select_from_table($this->tData->prefix."_users", array(), array("operator" => "", "conditions" => array("username" => $username)));

        // Check for results and return
        return $this->tData->count_rows($query) == 0 ? true : false;
    }


    /**
     * Defines the password
     *
     * @param string $password
     * @return string
     */
    protected function define_password($password) {
        // Check the password length
        if (strlen($password) < 4) {
            return "short";
        }

        return true;
    }


    /**
     * Defines the email address
     *
     * @param string $email
     * @return string
     */
    protected function define_email($email) {
        // Check the email via filter
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "invalid";
        }

        return true;
    }


    /**
     * Checks a given array against required key/values
     *
     * @param array $variables
     * @param array $required
     * @return boolean|array $return_variables
     */
    private function check_variables($variables, $required) {
        // Define the temporary and return variables
        $required_bool = true;
        $temp = $return_variables = array();

        // Loop through all of the variables
        foreach ($variables as $key => $value) {
            if (in_array($key, $required) && $value == "") {
                $required_bool = false;
                $temp[] = $key;
            } else {
                $return_variables[$key] = urldecode($value);
            }
        }

        // Check the temp array, return relevant
        if ($required_bool == false) {
            return $temp;
        } else {
            return $return_variables;
        }
    }


    /**
     * Defines the error result for the registration API call
     *
     * @param string $message
     * @return array
     */
    private function register_error($message = "") {
        return array("error"=>true,"response"=>alert_notify("danger", $message, "", true));
    }


    /**
     * Emails a newly created user their activation link
     *
     * @param strings $email
     * @param string $activation_code
     * @return boolean
     */
    private function email_registered_user($email, $activation_code) {
        // Get the site settings, for personalization
        $query = $this->tData->select_from_table($this->tData->prefix."_settings", array("name"));
        $settings = $this->tData->fetch_rows($query);

        // Create the email message
        $activation_addy = base_url."accounts/activate/&email=".$this->encode_string($email)."&code=$activation_code";
        $message = "You've recently registered to ".$settings['name']."!<br /><br />";
        $message .= "Now all you have to do is activate your account before you ".
            "can log in.<br />";
        $message .= "To activate your new account, <a href='$activation_addy'>click here</a>!";

        // Send the mail
        return tMail($email, "Activate Your Account", $message);
    }


    /**
     * Registers a user to the website
     *
     * @param array $args
     * @return array
     */
    protected function create_registered_user($args) {
        $required = array("username", "password", "password-repeat", "email", "first-name", "last-name");
        $user_variables = $this->check_variables($args, $required);

        // Check the user variables
        if ($this->tData->array_is_associative($user_variables) == false) {
            return array("error"=>true,"response"=>$user_variables);
        }

        // Check and define the username
        if ($this->define_username($user_variables['username']) !== true) {
            return $this->register_error("The username you've provided is ".$this->define_username($user_variables['username']).".");
        } elseif ($this->check_unused_username($user_variables['username']) != true) {
            return $this->register_error("That username has already been taken, try another.");
        } else {
            $user_variables['username'] = $user_variables['username'];
        }

        // Check and define the password
        if ($this->define_password($user_variables['password']) != true) {
            return $this->register_error("The password you've provided is too short.");
        } else {
            if ($user_variables['password'] != $user_variables['password-repeat']) {
                return $this->register_error("The passwords you've provided do not match.");
            } else {
                $salt = $this->tData->get_config_salt("password");
                $user_variables['password'] = hash('SHA256', $user_variables['password'].$salt);
            }
        }

        // Check and define the email address
        if ($this->define_email($user_variables['email']) != true) {
            return $this->register_error("The email address you've provided is not valid.");
        } else {
            $user_variables['email'] = $user_variables['email'];
        }

        // Check and define the first name
        if (strlen($user_variables['first-name']) > 50) {
            return $this->register_error("The first name is too long.  Use a nick name.");
        } else {
            $user_variables['first-name'] = $user_variables['first-name'];
        }

        // Check and define the last name
        if (strlen($user_variables['last-name']) > 125) {
            return $this->register_error("The last name is too long.");
        } else {
            $user_variables['last-name'] = $user_variables['last-name'];
        }

        // Define all other user registration information
        $user_variables['phone'] = "";
        $user_variables['birthday'] = date("Y-m-d");
        $user_variables['gender'] = "m";
        $user_variables['groups'] = "everyone";
        $user_variables['admin'] = "0";
        $user_variables['activation-code'] = md5(time());

        // Register the user
        $this->tData->use_pdo == false ? $this->tData->db->autocommit(false) : $this->tData->db->beginTransaction();
        $query = $this->tData->insert_table_row($this->tData->prefix."_users", array(
            "username"          => $user_variables['username'],
            "password"          => $user_variables['password'],
            "email"             => $user_variables['email'],
            "firstname"         => $user_variables['first-name'],
            "lastname"          => $user_variables['last-name'],
            "birthday"          => $user_variables['birthday'],
            "gender"            => $user_variables['gender'],
            "admin"             => $user_variables['admin'],
            "groups"            => $user_variables['groups'],
            "permanent"         => 0,
            "phone"             => $user_variables['phone'],
            "picture"           => "default-user-picture.png",
            "created"           => date("Y-m-d H:i:s"),
            "active"            => 0,
            "activation_code"   => $user_variables['activation-code']
        ));

        // Check the query and continue
        if (!$query) {
            return $this->register_error("There was an error registering you in the database. Please try again later.");
        } else {
            // Email the user
            if (!$this->email_registered_user($user_variables['email'], $user_variables['activation-code'])) {
                // Revert the database registration and notify the user
                $this->tData->use_pdo == false ? $this->tData->db->rollback() : $this->tData->db->rollBack();

                return $this->register_error("The activation email failed to send.  Try again later.");
            } else {
                 // Commit the registration to the database and notify the user
                $this->tData->db->commit();
                return array("error"=>false,"response"=>alert_notify("success", "You have been registered.  Check your email to activate your account!", "", true));
            }
        }
    }


    /**
     * Activates a user in the database based on their email address and activation code
     *
     * @param string $email
     * @param string $activation_code
     * @return array
     */
    protected function activate_user($email, $activation_code) {
        // Define the query data defaults
        $query_data = array("table_name" => $this->tData->prefix."_users", "data" => array(), "clause" => array());

        // Define the query data to find this user in the database
        $query_data['clause'] = array(
            "operator"  => "AND",
            "conditions"=> array("email" => $email, "activation_code" => $activation_code));

        // Try to find the user in the database
        $find_user_query = $this->tData->select_from_table($query_data['table_name'], array("id", "active"), $query_data['clause']);

        // Check the 'find user' query
        if ($find_user_query != false) {
            if ($this->tData->count_rows($find_user_query) > 0) {
                $user = $this->tData->fetch_rows($find_user_query);
            } else {
                return array("error" => true, "message" => "Couldn't find this user in the database.");
            }
        } else {
            return array("error" => true, "message" => "There was an error querying the database for this user.");
        }

        // Check if the user is activated already or not
        if ($user['active'] == 1) {
            return "active";
        }

        // Update the user's active status
        $query_data['data'] = array("active" => 1);
        $query_data['clause'] = array(
            "operator" => "",
            "conditions"=> array("id" => $user['id']));
        $update_user_query = $this->tData->update_table_row($query_data['table_name'], $query_data['data'], $query_data['clause']);

        if ($update_user_query != false) {
            return array("error" => false, "message" => "Your account has been activated! - <a href='accounts/login/'>You can login here</a>.");
        } else {
            return array("error" => true, "message" => "There was an issue when updating this user's active status.");
        }
    }


    protected function encode_string($string, $decode = false) {
        $replacements = array("." => "{p}", "-" => "{d}");
        foreach ($replacements as $key => $value) {
            $string = $decode == false ? str_replace($key, $value, $string) : str_replace($value, $key, $string);
        }

        return $string;
    }
}