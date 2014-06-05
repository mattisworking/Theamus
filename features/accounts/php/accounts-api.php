<?php

class AccountsApi extends Accounts {
    private $api_return = array("response"=>array("data"=>""), "error"=>array("status"=>0,"message"=>""));

    private function api_error($message = "") {
        $this->api_return['error'] = array("status"=>1,"message"=>$message);
    }

    public function logout() {
        session_destroy();
        setcookie("session", "", 30, "/");
        setcookie("userid", "", 30, "/");

        return true;
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
        $fetch_query = $this->tData->select_from_table($this->tData->prefix."_users", array('selector'),
            array("operator" => "AND", "conditions" => array("key" => "username", "value" => $username)));
        if ($this->tData->count_rows($fetch_query) == 0) {
            $this->api_error("Invalid credentials.");
            return $this->api_return;
        }

        // Define the user information
        $user_selector = $this->tData->fetch_rows($fetch_query);

        $user_query = $this->tData->select_from_table($this->tData->prefix.'_users', array(), array(
            'operator'      => '',
            'conditions'    => array('selector' => $user_selector['selector'])
        ));
        $user_results = $this->tData->fetch_rows($user_query);
        foreach ($user_results as $user_row) {
            $user[$user_row['key']] = $user_row['value'];
        }

        if ($user['password'] != $password) {
            $this->api_error('Invalid credentials');
            return $this->api_return;
        }

        // Check for an active user
        if ($user['active'] == 0) {
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
        if ($this->tUser->add_user_session($user['id'], $session, $expire)) {
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

    private function user_template() {
        return implode('', array(
            '<li>',
            '<ul class=\'user-options\'>',
            $this->tUser->has_permission('edit_users') ? '<li><a href=\'#\' name=\'edit-account-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
            $this->tUser->has_permission('remove_users') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-account-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
            '</ul>',
            '<span class=\'full-name\'>::stripslashes(trim(urldecode(\'%firstname% %lastname%\')))::</span>',
            '<span class=\'username\'>%username%</span>',
            '</li>'
        ));
    }

    public function get_user_accounts_list($args) {
        $user_accounts = parent::get_accounts();

        $this->tPages->set_page_data(array(
            'data'              => $user_accounts,
            'per_page'      	=> 25,
            'current'       	=> $args['page'],
            'list_template' 	=> $this->user_template()
        ));


        return '<ul class=\'accounts\'>'.$this->tPages->print_list(true).'</ul>'.$this->tPages->print_pagination('accounts_next_page', 'admin-pagination', true);
    }

    public function search_accounts($args) {
        if (!isset($args['search_query'])) {
            return alert_notify('danger', 'The search query was not found.', '', true);
        }

        if (!isset($args['page']) || !is_numeric($args['page'])) {
            $args['page'] = 1;
        }

        $searched_accounts = parent::search_for_accounts($args['search_query']);

        if (!is_array($searched_accounts)) {
            return $searched_accounts;
        }

        $this->tPages->set_page_data(array(
            'data'              => $searched_accounts,
            'per_page'      	=> 25,
            'current'       	=> $args['page'],
            'list_template' 	=> $this->user_template()
        ));

        return '<ul class=\'accounts\'>'.$this->tPages->print_list(true).'</ul>'.$this->tPages->print_pagination('accounts_next_page', 'admin-pagination', true);
    }

    public function create_account($args) {
        if ($this->tUser->has_permission('add_users') == false) {
            return 'You do not have permission to create accounts.';
        }

        $data = Accounts::check_account_parameters($args);

        if (!is_bool($data) && !is_array($data)) {
            return $data;
        }

        $data['change_password'] = true;

        return Accounts::create_account($data);
    }

    public function save_account_information($args) {
        if ($this->tUser->has_permission('edit_users') == false) {
            return 'You do not have permission to edit accounts.';
        }

        $data = Accounts::check_account_parameters($args, true);

        if (!is_bool($data) && !is_array($data)) {
            return $data;
        }

        return Accounts::save_account($data);
    }

    public function remove_account($args) {
        if ($this->tUser->has_permission('remove_users') == false) {
            return 'You do not have permission to remove accounts.';
        }

        if (!isset($args['id']) || $args['id'] == '') {
            return 'Invalid account ID provided (or not?)';
        }

        return Accounts::remove_account($args['id']);
    }
}