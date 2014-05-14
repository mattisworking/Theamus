<?php

class AccountsApi extends Accounts {
    private $api_return = array("response"=>array("data"=>""), "error"=>array("status"=>0,"message"=>""));

    private function api_error($message = "") {
        $this->api_return['error'] = array("status"=>1,"message"=>$message);
    }

    public function login($args) {
        // Define the salts
        $session_salt = $this->tData->get_config_salt("session");

        // Check the username
        if (isset($args['username'])) {
            if ($args['username'] != "") {
                $username = urldecode($args['username']);
            } else {
                $this->api_error("Please fill out the 'Username' field.");
                return $this->api_return;
            }
        } else {
            $this->api_error("There was an error finding the username variable.");
            return $this->api_return;
        }

        // Check the password
        if (isset($args['password'])) {
            if ($args['password'] != "") {
                $password = hash("SHA256", urldecode($args['password']).$this->tData->get_config_salt("password"));
            } else {
                $this->api_error("Please fill out the 'Password' field.");
                return $this->api_return;
            }
        } else {
            $this->api_error("There was an error finding the password variable.");
            return $this->api_return;
        }


        // Query the database for an existing user
        $fetch_query = $this->tData->select_from_table($this->tData->prefix."_users", array("active", "id"),
            array("operator" => "AND", "conditions" => array("username" => $username, "password" => $password)));
        if ($this->tData->count_rows($fetch_query) == 0) {
            $this->api_error("Invalid credentials.");
            return $this->api_return;
        }

        // Define the user information
        $row = $this->tData->fetch_rows($fetch_query);

        // Check for an active user
        if ($row['active'] == 0) {
            $this->api_error("Your account is not active.");
            return $this->api_return;
        }

        // Define a new session value
        $session = md5(time().$session_salt);

        // Cookie expiration time
        $expire = time() + 3600;
        if (isset($args['keep_session'])) {
            if ($args['keep_session'] == "true") {
                $expire = time() + (60 * 60 * 24 * 14);
            }
        }

        // Update the user's session in the database
        if ($this->tUser->add_user_session($row['id'], $session, $expire)) {
            return true;
        } else {
            $this->api_error("There was an error updating/creating the session.");
            return $this->api_return;
        }
    }

    public function check_username($args) {
        // Check for a username
        if (!isset($args['username']) || $args['username'] == "") {
            return "invalid";
        }

        // Return the accounts class username check
        return $this->define_username(urldecode($args['username']));
    }

    public function check_password($args) {
        // Check for a password
        if (!isset($args['password']) || $args['password'] == "") {
            return "invalid";
        }

        // Return the accounts class username check
        return $this->define_password(urldecode($args['password']));
    }

    public function check_email($args) {
        // Check for an email
        if (!isset($args['email']) || $args['email'] == "") {
            return "invalid";
        }

        // Return the accounts class username check
        return $this->define_email(urldecode($args['email']));
    }

    public function register_user($args) {
        return $this->create_registered_user($args);
    }


    /**
     * Defines the information to send out to the parent class, then runs a parent
     * function to activate a user
     *
     * @param array $args
     * @return array
     */
    public function activate_user($args) {
        // Define or fail on the email address
        if ($args['email'] == "") {
            return array("error" => true, "message" => "Couldn't activate because there is no email address defined.");
        } else {
            $email = parent::encode_string(urldecode($args['email']), true);
        }

        // Define or fail on the activation code
        if ($args['code'] == "") {
            return array("error" => true, "message" => "Couldn't activation because there is no activation code defined.");
        } else {
            $code = parent::encode_string(urldecode($args['code']), true);
        }

        // Activate the user
        return parent::activate_a_user($email, $code);
    }
}