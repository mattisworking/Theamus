<?php

class AccountsApi extends Accounts {
    /**
     * Sets up return data to be sent to the client if there is an error
     *
     * @param string $message
     * @return array
     */
    private function api_error($message = '') {
        return array('response'=>array('data' => ''), 'error' => array('status'=>1,'message'=>$message));
    }


    /**
     * Logs a user out, destroying their session and cookies
     *
     * @return boolean
     */
    public function logout() {
        // Remove the session and cookies
        session_destroy();
        setcookie('session', '', 30, '/');
        setcookie('userid', '', 30, '/');

        return true; // Return
    }


    /**
     * Logs a user in, creating a session and setting cookies
     *
     * @param array $args
     * @return array|boolean
     */
    public function login($args) {
        // Define the configured salts
        $session_salt = $this->tData->get_config_salt('session');

        // Validate the username
        if (isset($args['username'])) {
            if ($args['username'] == '') {
                return $this->api_error('Please fill out the \'Username\' field.');
            }
        } else {
            return $this->api_error('There was an error finding the username variable.');
        }

        // Validate the password
        if (isset($args['password'])) {
            if ($args['password'] == '') {
                return $this->api_error('Please fill out the \'Password\' field.');
            }
        } else {
            return $this->api_error('There was an error finding the password variable.');
        }

        // Define the username and password
        $username = urldecode($args['username']);
        $password = hash('SHA256', urldecode($args['password']).$this->tData->get_config_salt('password'));


        // Query the database to check the existance of the given username
        $selector_query = $this->tData->select_from_table($this->tData->prefix.'_users', array('selector'), array(
            'operator'      => 'AND',
            'conditions'    => array('key' => 'username', 'value' => $username)
        ));

        // Check for query results
        if ($this->tData->count_rows($selector_query) == 0) {
            return $this->api_error('Invalid username.');
        }

        // Define the selectors related to the provided username
        $selector = $this->tData->fetch_rows($selector_query);

        // Query the database for all of the information related to the found selector
        $user_query = $this->tData->select_from_table($this->tData->prefix.'_users', array(), array(
            'operator'      => '',
            'conditions'    => array('selector' => $selector['selector'])
        ));

        // Define the user information
        $user = $this->convert_keyval_to_associative($this->tData->fetch_rows($user_query));

        // Check the user's password against the database
        if ($user[$selector['selector']]['password'] != $password) {
            return $this->api_error('Invalid credentials');
        }

        // Check the user's active status
        if ($user[$selector['selector']]['active'] == 0) {
            return $this->api_error('This account is not active. Please contact an adminsitrator to have it activated.');
        }

        // Define a new session value and the cookie expiration time
        $session = md5(time().$session_salt);
        $expire = time() + 3600;
        if (isset($args['keep_session'])) {
            if ($args['keep_session'] == 'true') {
                $expire = time() + (60 * 60 * 24 * 14); // Two weeks from NOW
            }
        }

        // Update the user's session in the database
        if (!$this->tUser->add_user_session($user[$selector['selector']]['id'], $session, $expire)) {
            return $this->api_error('There was an error updating/creating the session.');
        }

        return true; // Return
    }


    /**
     * Checks for a valid username when a user is registering.
     *  As they are typing, this provides the data to show a check or X in the text box
     *
     * @param array $args
     * @return string
     */
    public function check_username($args) {
        // Validate the given username, making sure it exists and isn't blank
        if (!isset($args['username']) || $args['username'] == '') {
            return 'invalid';
        }

        // Check the username on a higher level, for it's existence in the database or illegal characters and return the value
        return $this->define_username(urldecode($args['username']));
    }


    /**
     * Checks for a valid password when a user is registering.
     *  As they are typing, this provides the data to show a check or X in the text box
     *
     * @param array $args
     * @return string
     */
    public function check_password($args) {
        // Validate the given password, making sure it exists and isn't blank
        if (!isset($args['password']) || $args['password'] == '') {
            return 'invalid';
        }

        // Check the password on a higher level, for length, mostly
        return $this->define_password(urldecode($args['password']));
    }


    /**
     * Checks for a valid email address when a user is registering.
     *  As they are typing, this provides the data to show a check or X in the text box
     *
     * @param array $args
     * @return string
     */
    public function check_email($args) {
        // Validate the given email address, making sure it exists and isn't blank
        if (!isset($args['email']) || $args['email'] == '') {
            return 'invalid';
        }

        // Check the password on a higher level, validating it as an address
        return $this->define_email(urldecode($args['email']));
    }


    /**
     * Runs the parent (Accounts) function to register users to the website
     *
     * @param array $args
     * @return array|boolean
     */
    public function register_user($args) {
        // Validate the required registration parameters
        $data = $this->check_register_parameters($args);

        // Return based on the results of the validation above ^^
        if (!is_bool($data) && !is_array($data)) {
            return $data;
        }

        $data['change_password'] = true; // Pay attention to the password

        // Return the value that was returned by trying to register a user
        return $this->create_account($data, true);
    }


    /**
     * Defines the information to send out to the parent class, then runs a parent
     * function to activate a user
     *
     * @param array $args
     * @return array
     */
    public function activate_user($args) {
        // Validate the given email address, making sure it exists and it isn't empty
        if (!isset($args['email']) || $args['email'] == '') {
            return array('error' => true, 'message' => 'Couldn\'t activate because there is no email address defined.');
        }

        // Validate the activation code, making sure it exists and it isn't empty
        if (!isset($args['code']) || $args['code'] == '') {
            return array('error' => true, 'message' => 'Couldn\'t activation because there is no activation code defined.');
        }

        // Define the email address and activation code
        $email = urldecode($args['email']);
        $code = urldecode($args['code']);

        // Return the value that was returned by trying to activate a user
        return $this->activate_a_user($email, $code);
    }


    /**
     * Defines a template to use when listing users out in the window
     *
     * @return string
     */
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


    /**
     * Defines user accounts into a presentable list for the administrator
     *
     * @param array $args
     * @return string
     */
    public function get_user_accounts_list($args) {
        // Define the page data information that will set up the return data
        $this->tPages->set_page_data(array(
            'data'              => $this->get_accounts(),
            'per_page'      	=> 25,
            'current'       	=> $args['page'],
            'list_template' 	=> $this->user_template()
        ));

        // Return the list of users
        return '<ul class=\'accounts\'>'.$this->tPages->print_list(true).'</ul>'.$this->tPages->print_pagination('accounts_next_page', 'admin-pagination', true);
    }


    /**
     * Calls a parent(Accounts) class function that performs a search for users related to this website
     *  and returns the information in a list format
     *
     * @param array $args
     * @return string|array
     */
    public function search_accounts($args) {
        // Validate the search query, making sure it exists
        if (!isset($args['search_query'])) {
            return alert_notify('danger', 'The search query was not found.', '', true);
        }

        // Define the current page number
        $args['page'] = !isset($args['page']) || !is_numeric($args['page']) ? 1 : $args['page'];

        // Search for the accounts in the database
        $searched_accounts = $this->search_for_accounts($args['search_query']);

        // Check if there are users to show - if not, show the results
        if (!is_array($searched_accounts)) {
            return $searched_accounts;
        }

        // Define the page data information that will set up the return data
        $this->tPages->set_page_data(array(
            'data'              => $searched_accounts,
            'per_page'      	=> 25,
            'current'       	=> $args['page'],
            'list_template' 	=> $this->user_template()
        ));

        // Return the list of users
        return '<ul class=\'accounts\'>'.$this->tPages->print_list(true).'</ul>'.$this->tPages->print_pagination('accounts_next_page', 'admin-pagination', true);
    }


    /**
     * Calls a parent(Accounts) class function that creates a new user in the database
     *
     * @param array $args
     * @return string|boolean
     */
    public function create_new_account($args) {
        // Check for the user's ability to add users
        if ($this->tUser->has_permission('add_users') == false) {
            return 'You do not have permission to create accounts.';
        }

        // Validate the fields required to create a new account
        $data = $this->check_account_parameters($args);

        // Check the validation above and return relevant information
        if (!is_bool($data) && !is_array($data)) {
            return $data;
        }

        $data['change_password'] = true; // Change the password (create)

        // Return the data returned by creating an account from the parent class
        return $this->create_account($data);
    }


    /**
     * Calls a parent(Accounts) class function that saves account information to the database
     *
     * @param array $args
     * @return string|boolean
     */
    public function save_account_information($args) {
        // Check for the user's ability to edit users
        if ($this->tUser->has_permission('edit_users') == false) {
            return 'You do not have permission to edit accounts.';
        }

        // Validate the fields required to save account information
        $data = $this->check_account_parameters($args, true);

        // Check the validation above and return relevant information
        if (!is_bool($data) && !is_array($data)) {
            return $data;
        }

        // Return the data returned by saving the account information function from the parent class
        return $this->save_account($data);
    }


    /**
     * Calls a parent(Accounts) class function that removes a user from the database
     *
     * @param array $args
     * @return string|boolean
     */
    public function remove_user_account($args) {
        // Check for the user's ability to remove users
        if ($this->tUser->has_permission('remove_users') == false) {
            return 'You do not have permission to remove accounts.';
        }

        // Validate the given id, making sure it exists and has a value
        if (!isset($args['id']) || $args['id'] == '') {
            return 'Invalid account ID provided (or not?)';
        }

        // Return the data returned by the remove_account function from the parent class
        return $this->remove_account($args['id']);
    }
}