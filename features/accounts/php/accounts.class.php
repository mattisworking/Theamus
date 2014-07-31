<?php

class Accounts {
    /**
     * Theamus data manipulation class
     *
     * @var object $tData
     */
    protected $tData;


   /**
     * Theamus user class
     *
     * @var object $tUser
     */
    protected $tUser;


    /**
     * Starts the class connecting to classes that will be needed throughout
     *
     * @return
     */
    public function __construct() {
        $this->initialize_variables();
        return;
    }


    /**
     * Connects to the database, defines the user and pagniation classes as well
     *
     * @return
     */
    private function initialize_variables() {
        // Connect to the database
        $this->tData            = new tData();
        $this->tData->db        = $this->tData->connect(true);
        $this->tData->prefix    = DB_PREFIX;

        $this->tUser            = new tUser();  // User data class
        $this->tPages           = new tPages(); // Pagination class
        return;
    }


    /**
     * Checks a variable to see if it is a valid username
     *
     * @param string $username
     * @return string
     */
    protected function define_username($username) {
        // Check for illegal characters
        if (preg_match('/[^a-zA-Z0-9.-_@\[\]:;]/', $username)) {
            return 'invalid';

        // Check the username length
        } elseif (strlen($username) > 25 || strlen($username) < 4) {
            return 'invalid';

        // Check for and existing username
        } elseif ($this->check_unused_username($username) == false) {
            return 'taken';
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
        $query = $this->tData->select_from_table($this->tData->prefix.'users', array(), array('operator' => 'AND', 'conditions' => array('key' => 'username', 'value' => $username)));

        // Check for results and return
        return $this->tData->count_rows($query) == 0 ? true : false;
    }


    /**
     * Checks for a valid phone number from a string
     *
     * 1 (555) 555-1234 -> 5555551234
     *
     * @param string $number
     * @return string
     */
    protected function check_phone($number = '') {
        // Check for a given phone number
        if ($number != '') {
            // Define the phone number and remove anything that isn't a number
            $phone = urldecode($number);
            $numbers = preg_replace('/[^0-9]/', '', $phone);

            // Check for numeric numbers only
            if (!is_numeric($numbers)) return '';

            // Check for a leading 1 and remove it if there is one
            if (strlen($numbers) >= 10 && strlen($numbers) <= 11) {
                $numbers = preg_replace('/^1/', '',$numbers);
            }

            // Check for a proper phone number lenght and check if it is numeric or not
            if (strlen($numbers) == 10 && is_numeric($numbers)) {
                $phone = $numbers;
            }

            return $phone; // Return the phone number
        }

        return ''; // Return nothing, by default
    }


    /**
     * Defines the password
     *
     * @param string $password
     * @return string
     */
    protected function define_password($password = '') {
        // Check the password length
        if (strlen($password) < 4) {
            return 'short';
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
            return 'invalid';
        }

        return true;
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
        $query = $this->tData->select_from_table($this->tData->prefix.'settings', array('name'));
        $settings = $this->tData->fetch_rows($query);

        // Create the email message
        $activation_addy = base_url.'accounts/activate/&email='.$email.'&code='.$activation_code;
        $message = 'You\'ve recently registered to '.$settings['name'].'!<br /><br />';
        $message .= 'Now all you have to do is activate your account before you can log in.<br />';
        $message .= 'To activate your new account, <a href=\''.$activation_addy.'\'>click here</a>!';

        // Send the mail
        return tMail($email, 'Activate Your Account', $message);
    }


    /**
     * Activates a user in the database based on their email address and activation code
     *
     * @param string $email
     * @param string $activation_code
     * @return array
     */
    protected function activate_a_user($email, $activation_code) {
        // Define the query data defaults
        $query_data = array('table_name' => $this->tData->prefix.'users', 'data' => array());

        // Query the database to find the user with the actvation code provided
        $selector_query = $this->tData->select_from_table($query_data['table_name'], array('selector'), array('operator' => 'AND', 'conditions' => array('key' => 'activation_code', 'value' => $activation_code)));
        if ($selector_query == false || $this->tData->count_rows($selector_query) == 0 || $this->tData->count_rows($selector_query) > 1) {
            return array('error' => true, 'message' => 'Couldn\'t find this user in the database.');
        }

        // Define the selector related to the activation code
        $selector_results = $this->tData->fetch_rows($selector_query);
        $selector = $selector_results['selector'];

        // Try to find the user in the database
        $find_user_query = $this->tData->select_from_table($query_data['table_name'], array(), array('operator' => '', 'conditions' => array('selector' => $selector)), 'ORDER BY `id` DESC');
        if ($find_user_query == false || $this->tData->count_rows($find_user_query) == 0) {
            return array('error' => true, 'message' => 'Couldn\'t find this user in the database.');
        }

        // Define the user information based on the selector found from the activation code
        $user = $this->convert_keyval_to_associative($this->tData->fetch_rows($find_user_query));

        // Check the email address against the email address related to the selector found from the activation code in the database
        if ($email != $user[$selector]['email']) {
            return array('error' => true, 'message' => 'Bad email/activation code combination.');
        }

        // Check if the user is activated already or not
        if ($user[$selector]['active'] == 1) {
            return 'active';
        }

        // Update the user's active status
        $update_user_query = $this->tData->update_table_row($query_data['table_name'], array('value' => 1), array('operator' => 'AND', 'conditions'=> array('key' => 'active', 'selector' => $selector)));

        // Check the activation query
        if ($update_user_query == false) {
            return array('error' => true, 'message' => 'There was an issue when updating this user\'s active status.');
        }

        // Return positive
        return array('error' => false, 'message' => 'Your account has been activated! - <a href=\'accounts/login/\'>You can login here</a>.');
    }


    /**
     * Define the accounts tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function accounts_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('List of Users', 'admin/index.php', 'Theamus Accounts'),
            array('Search Users', 'admin/search-accounts.php', 'Search Accounts'),
            array('Create a New User', 'admin/create-account.php', 'Create a New Account')
        );

        $return_tabs = array(); // Empty return array to add to

        // Loop through all of the tabs defined above and assign them to li items/links
        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : ''; // Define the current tab
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'accounts-tab\' data-file=\'accounts/'.trim($tab[1], '.php').'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        // Return the tabs to the page
        return '<ul>'.implode('', $return_tabs).'</ul>';
    }


    /**
     * Takes information from the database in the form of `key` = 'key' and `value` = 'value' and turns it into 'key' = 'value'
     *
     * @param array $data
     * @return array $return_data
     */
    protected function convert_keyval_to_associative($data) {
        $return_data = array(); // Empty return array to add to

        // Loop through all of the given information adding the information relevantly
        foreach ($data as $item) {
            $return_data[$item['selector']][$item['key']] = $item['value'];
        }

        // Return the newly defined data
        return $return_data;
    }


    /**
     * Get all of the accounts from the database
     *
     * @return array
     */
    protected function get_accounts() {
        // Define the query data that will find the users in the database
        $query_data = array(
            'table_name'    => $this->tData->prefix.'users',
            'clause'        => array('operator' => '', 'conditions' => array()));

        // Query the database for all of the users
        $query = $this->tData->select_from_table($query_data['table_name'], array(), array(), 'ORDER BY `selector`');

        // Define the user information
        $user_results = $this->tData->fetch_rows($query);

        // Return the users
        return $this->convert_keyval_to_associative($user_results);
    }


    /**
     * Searches the database for users based on their username, firstname or lastname
     *
     * @param string $search_query
     * @return string|array $users
     */
    protected function search_for_accounts($search_query = '') {
        // Check the search query and return if it is empty
        if ($search_query == '') {
            return '';
        }

        // Define the query information that will be used to find the user in the database based on the search query provided
        $query_data = array(
            'table'     => $this->tData->prefix.'users',
            'clause'    => array(
                'operator'      => 'OR',
                'conditions'    => array(
                    array(
                        'operator'      => 'AND',
                        'conditions'    => array('key' => 'username', '[%]value' => $search_query.'%')
                    ),
                    array(
                        'operator'      => 'AND',
                        'conditions'    => array('key' => 'firstname', '[%]value' => $search_query.'%')
                    ),
                    array(
                        'operator'      => 'AND',
                        'conditions'    => array('key' => 'lastname', '[%]value' => $search_query.'%')
                    )
                )
            )
        );

        // Query the database for results
        $selector_query = $this->tData->select_from_table($query_data['table'], array('selector'), $query_data['clause']);

        // Check the results and for results, return respectively
        if ($selector_query == false || $this->tData->count_rows($selector_query) == 0) {
            return alert_notify('info', 'No accounts were found.', '', true);
        }

        // Define the selectors related to the user's found in the database based on the search query
        $selector_results = $this->tData->fetch_rows($selector_query);
        $selectors = isset($selector_results[0]) ? $selector_results : array($selector_results);

        // Define empty arrays to add to
        $used_selectors = $users = $user_clauses = array();

        // Loop through all of the selectors, adding to the clause data to find the users with
        foreach ($selectors as $selector) {
            // Press on if this selector has already been added to the clause data
            if (in_array($selector['selector'], $used_selectors)) {
                continue;
            }

            // Define the clause data to find information about this user
            $user_clauses[] = array('operator' => '', 'conditions' => array('selector' => $selector['selector']));
        }

        // Query the database for the user information
        $user_query = $this->tData->select_from_table($query_data['table'], array('key', 'value', 'selector'), array(
            'operator'      => 'OR',
            'conditions'    => $user_clauses
        ), 'ORDER BY `id` ASC');

        // Check for a valid query
        if ($user_query != false) {
            // Define the user results
            $user_results = $this->tData->fetch_rows($user_query);
            $users = $this->convert_keyval_to_associative($user_results);
        }

        // Return the found users
        return $users;
    }


    /**
     * Checks the arguments given by the front end for existence and validity
     *
     * @param array $args
     * @param boolean $edit
     * @return string|array $args
     */
    protected function check_account_parameters($args, $edit = false) {
        // Define the required variables to check for
        $required = array(
            array('First Name', 'firstname'),
            array('Last Name', 'lastname'),
            array('Gender', 'gender'),
            array('Birthday Month', 'bday_month'),
            array('Birthday Day', 'bday_day'),
            array('Birthday Year', 'bday_year'),
            array('Email', 'email'),
            array('Groups', 'groups'),
            array('Administrator', 'is_admin')
        );

        // Define more variables based on where the request is coming from
        if ($edit == true) {
            $required[] = array('Change Password', 'change_password');
        } else {
            $required[] = array('Username', 'username');
        }

        // Loop through the requried variables and check the given arguments
        foreach ($required as $parameter) {
            if (!isset($args[$parameter[1]]) || $args[$parameter[1]] == '') {
                return alert_notify('danger', 'Please fill out the \''.$parameter[0].'\' field.', '', true);
            }
        }

        // Return the arguments
        return $args;
    }


    /**
     * Checks the arguments given by the registration form
     *
     * @param arguments $args
     * @return array $args
     */
    protected function check_register_parameters($args) {
        // Define the required variables to check for
        $required = array(
            array('Username', 'username'),
            array('First Name', 'firstname'),
            array('Last Name', 'lastname'),
            array('Email', 'email'),
        );

        // Loop through the requried variables and check the given arguments
        foreach ($required as $parameter) {
            if (!isset($args[$parameter[1]]) || $args[$parameter[1]] == '') {
                return alert_notify('danger', 'Please fill out the \''.$parameter[0].'\' field.', '', true);
            }
        }

        // Return the arguments
        return $args;
    }


    /**
     * Simply decodes something that might have been encoded in the front end via JS
     *
     * @param string $str
     * @return string $decoded
     */
    protected function decode($str) {
        $decoded = ''; // Define the decoded string variable

        // Loop throught the given string
        for($i = 0; $i < strlen($str); $i++) {
            // Get the reverse ASCII character of the encoded string
            $b = ord($str[$i]);
            $a = $b ^ 123;
            $decoded .= chr($a);
        }

        // Return the decoded string
        return $decoded;
    }


    /**
     * Checks any given data for their expected values and returns the data respectively
     *
     * @param array $data
     * @param boolean $edit
     * @param boolean $registration
     * @return string|array $user_variables
     */
    protected function sanitize_account_variables($data, $edit = false, $registration = false) {
        // Check the request - continue on if it's not an 'edit' request
        if ($edit == true) {
            // Check for the user's ID
            if (!isset($data['id']) || $data['id'] == '') {
                return alert_notify('danger', 'Invalid account ID provided (or not?)', '', true);
            }

            // Define the user ID in the return array
            $user_variables['id'] = $this->decode($data['id']);

            // Define the user active variable
            $user_variables['active'] = !isset($data['active']) || $data['active'] == '' || is_bool($data['active']) ? 1 : 0;
        }

        // Check the request - conting on if it IS an 'edit' request'
        if ($edit == false) {
            // Check and define the username
            $defined_username = $this->define_username($data['username']);

            // Check the check and return or define the username
            if ($defined_username !== true) {
                return alert_notify('danger', 'The username you\'ve provided is '.$defined_username.'.', '', true);
            } else {
                $user_variables['username'] = $data['username'];
            }

            // Define the activation code
            $user_variables['activation-code'] = md5(time());
        }

        // Check and define the password
        if (is_bool($data['change_password'])) {
            // Check for a password
            if ($data['password'] == '') {
                return alert_notify('danger', 'Please fill out the \'Password\' field.');
            }

            // Check for the repeat password
            if ($data['password_again'] == '') {
                return alert_notify('danger', 'Please fill out the \'Password Again\' field.');
            }

            // Check the password
            $defined_password = $this->define_password($data['password']);

            // Check the check and return if it is a bad password
            if (!is_bool($defined_password)) {
                return alert_notify('danger', 'The password provided is too short.', '', true);
            } else {
                // Check the password against the repeated password and return
                if ($data['password'] != $data['password_again']) {
                    return alert_notify('danger', 'The passwords provided do not match.', '', true);
                } else {
                    // Define the encrypted password
                    $salt = $this->tData->get_config_salt('password');
                    $user_variables['password'] = hash('SHA256', $data['password'].$salt);
                }
            }
        }

        // Check and define the email address
        if (!is_bool($this->define_email($data['email']))) {
            return alert_notify('danger', 'The email address you\'ve provided is not valid.', '', true);
        } else {
            $user_variables['email'] = $data['email'];
        }

        // Check and define the first name
        if (strlen($data['firstname']) > 50) {
            return alert_notify('danger', 'The first name is too long.  Use a nick name.', '', true);
        } else {
            $user_variables['firstname'] = $data['firstname'];
        }

        // Check and define the last name
        if (strlen($data['lastname']) > 125) {
            return alert_notify('danger', 'The last name is too long.', '', true);
        } else {
            $user_variables['lastname'] = $data['lastname'];
        }

        // Define all other user registration information
        $user_variables['phone'] = $registration == false ? $this->check_phone($data['phone']) : '';
        $user_variables['birthday'] = $registration == false ? $data['bday_year'].'-'.$data['bday_month'].'-'.$data['bday_day'] : date('Y-m-d');
        $user_variables['gender'] = $registration == false ? $data['gender'] : 'm';
        $user_variables['groups'] = $registration == false ? $data['groups'] : 'everyone,basic_users';
        $user_variables['admin'] = 0;
        if ($registration == false) {
            $user_variables['admin'] = $data['is_admin'] != 1 ? 0 : 1;
        }

        // Return the user information
        return $user_variables;
    }


    /**
     * Creates a user account
     *
     * @param array $data
     * @param boolean $registration
     * @return string|boolean
     */
    protected function create_account($data, $registration = false) {
        // Check for an administrator
        if (!$registration && (!$this->tUser->is_admin() || !$this->tUser->has_permission('add_users'))) {
            die('You must be an administrator to do this.');
        }

        // Sanitize and check the given variables
        $user_variables = $this->sanitize_account_variables($data, false, $registration);

        // Check the user variables and return the error the check gave, if there is one
        if (is_array($user_variables) == false) {
            return $user_variables;
        }

        // Find the system's email host in the database
        $system_query = $this->tData->select_from_table($this->tData->prefix.'settings', array('email_host'));
        $system = $this->tData->fetch_rows($system_query);

        // Query the database for all of the user's selectors
        $selector_query = $this->tData->select_from_table($this->tData->prefix.'users', array('selector'), array(), 'GROUP BY `selector` ORDER BY `selector` DESC LIMIT 1');

        // Define a new selector for the new user
        if ($selector_query == false) {
            $selector = time();
        } else {
            $selector_data = $this->tData->fetch_rows($selector_query);
            $selector = $selector_data['selector'] + 1;
        }

        // Create the user in the database
        $this->tData->use_pdo == true ? $this->tData->db->beginTransaction() : $this->tData->db->autocommit(true);
        $query = $this->tData->insert_table_row($this->tData->prefix.'users', array(
            array('key' => 'id', 'value' => $selector, 'selector' => $selector),
            array('key' => 'username', 'value' => $user_variables['username'], 'selector' => $selector),
            array('key' => 'password', 'value' => $user_variables['password'], 'selector' => $selector),
            array('key' => 'email', 'value' => $user_variables['email'], 'selector' => $selector),
            array('key' => 'firstname', 'value' => $user_variables['firstname'], 'selector' => $selector),
            array('key' => 'lastname', 'value' => $user_variables['lastname'], 'selector' => $selector),
            array('key' => 'birthday', 'value' => $user_variables['birthday'], 'selector' => $selector),
            array('key' => 'gender', 'value' => $user_variables['gender'], 'selector' => $selector),
            array('key' => 'admin', 'value' => $user_variables['admin'], 'selector' => $selector),
            array('key' => 'groups', 'value' => $user_variables['groups'], 'selector' => $selector),
            array('key' => 'permanent', 'value' => 0, 'selector' => $selector),
            array('key' => 'phone', 'value' => $user_variables['phone'], 'selector' => $selector),
            array('key' => 'picture', 'value' => 'default-user-picture.png', 'selector' => $selector),
            array('key' => 'created', 'value' => date('Y-m-d H:i:s'), 'selector' => $selector),
            array('key' => 'active', 'value' => $registration == false || $system['email_host'] == '' ? 1 : 0, 'selector' => $selector),
            array('key' => 'activation_code', 'value' => $user_variables['activation-code'], 'selector' => $selector)
        ));

        // Check the query and continue
        if ($query == false) {
            // Rollback the new rows and return an error
            $this->tData->use_pdo == true ? $this->tData->db->rollBack() : $this->tData->db->rollback();
            return alert_notify('danger', 'There was an error registering you in the database. Please try again later.', '', true);
        } else {
            // Check if it's a registered user and there is an email host defined
            if ($registration == true && $system['email_host'] != '') {
                // Try to email the new user their activation code
                if (!$this->email_registered_user($user_variables['email'], $user_variables['activation-code'])) {
                    // Rollback the new user rows and return an error if the email failed to send
                    $this->tData->use_pdo == true ? $this->tData->db->rollBack() : $this->tData->db->rollback();
                    return alert_notify('danger', 'There was an error registering you. Please try again later.', '', true);
                }
            }

            // Commit the new user to the database and return positively
            $this->tData->use_pdo == true ? $this->tData->db->commit() : $this->tData->db->commit();
            return true;
        }
    }


    /**
     * Saves edited user account information in the database
     *
     * @param array $data
     * @return string|boolean
     */
    public function save_account($data) {
        // Check for an administrator
        if (!$this->tUser->is_admin() || !$this->tUser->has_permission('edit_users')) die('You must be an administrator to do this.');

        // Sanitize and check the given variables
        $user_variables = $this->sanitize_account_variables($data, true);

        // Check the user variables and return the error the check gave, if there is one
        if (is_array($user_variables) == false) {
            return $user_variables;
        }

        // Define the query data that will properly update the user information
        $query_data = array(
            'table'     => $this->tData->prefix.'users',
            'data'      => array(
                array('value' => $user_variables['email']),
                array('value' => $user_variables['firstname']),
                array('value' => $user_variables['lastname']),
                array('value' => $user_variables['phone']),
                array('value' => $user_variables['birthday']),
                array('value' => $user_variables['gender']),
                array('value' => $user_variables['groups']),
                array('value' => $user_variables['admin']),
                array('value' => $user_variables['active'])
            ),
            'clause'    => array(
                array('operator' => 'AND', 'conditions' => array('key' => 'email', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'firstname', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'lastname', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'phone', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'birthday', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'gender', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'groups', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'admin', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'active', 'selector' => $user_variables['id']))
            )
        );

        // Check if the user is changing their password and add it to the update query data
        if (isset($user_variables['password'])) {
            $query_data['data'][] = array('value' => $user_variables['password']);
            $query_data['clause'][] = array('operator' => 'AND', 'conditions' => array('key' => 'password', 'selector' => $user_variables['id']));
        }

        // Query the database, updating the information provided
        $query = $this->tData->update_table_row($query_data['table'], $query_data['data'], $query_data['clause']);

        // Check the query and return respectively
        if ($query == false) {
            return alert_notify('danger', 'There was an issue with saving this information to the database.', '', true);
        }

        return true; // Return postively
    }

    /**
     * Removes a user from the database
     *
     * @param string $id
     * @return string|boolean
     */
    public function remove_account($id) {
        // Check for an administrator
        if (!$this->tUser->is_admin() || !$this->tUser->has_permission('remove_users')) die('You must be an administrator to do this.');

        // Check the given ID and return respectively
        if ($id == '') {
            return alert_notify('danger', 'The given user id is invalid.', '', true);
        }

        // Remove the user's information from the database
        $query = $this->tData->delete_table_row($this->tData->prefix.'users', array('operator' => '', 'conditions' => array('selector' => Accounts::decode($id))));

        // Check the query and return respectively
        if ($query == false) {
            return alert_notify('danger', 'There was an issue removing this account from the database.', '', true);
        }

        return true; // Return positively
    }


    /**
     * Checks for a logged in user
     *
     * @return boolean
     */
    private function check_user() {
        return $this->tUser->user != false ? true : false;
    }


    /**
     * Saves the currently logged in user's edited information to the database
     *
     * @param array $user
     * @return string|boolean
     */
    public function save_current_account($user) {
        // Boot the user if they aren't logged in
        if (!$this->check_user()) die('You must be logged in to save account information.');

        // Define default variables
        $query_data = array();

        // Update the user's profile picture
        if (!empty($_FILES)) {
            $picture = $_FILES['picture'];

            // Get the filetype
            $filetype_array = explode('.', $picture['name']);
            $filetype = strtolower($filetype_array[count($filetype_array) - 1]);

            // Define the file name
            $filename = md5(time().$picture['name']).'.'.$filetype;

            // Check filetype
            $allowed_types = array('jpg', 'png', 'jpeg', 'bmp', 'tiff');
            if (!in_array($filetype, $allowed_types)) {
                alert_notify('danger', 'Invalid type of profile picture uploaded.', '', true);
            }

            // Upload the file
            if (move_uploaded_file($picture['tmp_name'], path(ROOT.'/media/profiles/'.$filename))) {
                $query_data['data'][]   = array('value' => $filename);
                $query_data['clause'][] = array(
                    'operator'      => 'AND',
                    'conditions'    => array('key' => 'picture', 'selector' => $this->tUser->user['id']));
            } else {
                alert_notify('danger', 'There was an error uploading your picture. This may be because of file permissions.', '', true);
            }
        }

        // Save the user's password
        if (is_bool($user['change_password'])) {
            // Check for a password
            if (!isset($user['password']) || $user['password'] == '') {
                return alert_notify('danger', 'Please fill out the password field.', '', true);
            }

            // Check for a repeated password
            if (!isset($user['password_again']) || $user['password_again'] == '') {
                return alert_notify('danger', 'Please fill out the password field.', '', true);
            }

            // Check the password length
            if ($this->define_password($user['password']) !== true) {
                return alert_notify('danger', 'The password provided is too short.', '', true);
            }

            // Check the passwords against eachother
            if ($user['password'] != $user['password_again']) {
                return alert_notify('danger', 'The passwords provided do not match.', '', true);
            }

            // Define the salt to encrypt the password with
            $salt = $this->tData->get_config_salt('password');

            // Update the database
            $query_data['data'][] = array('value' => hash('SHA256', $user['password'].$salt));
            $query_data['clause'][] = array(
                    'operator'      => 'AND',
                    'conditions'    => array('key' => 'password', 'selector' => $this->tUser->user['id']));
        }

        // Update the database
        if (isset($query_data['data']) && isset($query_data['clause'])) {
            $update_query = $this->tData->update_table_row($this->tData->prefix.'users', $query_data['data'], $query_data['clause']);

            // Check the update query
            if(!$update_query) {
                return alert_notify('danger', 'There was an issue when saving this information to the database.', '', true);
            }
        }

        return true;
    }


    /**
     * Changes a user's profile picture back to the default picture
     *
     * @return string|boolean
     */
    public function remove_user_picture() {
        // Check for a logged in user
        if (!$this->check_user()) die('You must be logged in to remove account profile pictures.');

        // Check the user's profile picture for the default picture
        if ($this->tUser->user['picture'] != 'default-user-picture.png') {
            return alert_notify('danger', 'You cannot remove the default profile picture.', '', true);
        }

        // Remove the user's picture from the folder
        if (!unlink(path(ROOT.'/media/profiles/'.$this->tUser->user['picture']))) {
            return alert_notify('danger', 'There was an error deleting the picture from the pictures folder.', '', true);
        }

        // Update the table row to reflect the change
        $query = $this->tData->update_table_row($this->tData->prefix.'users',
            array('value' => 'default-user-picture.png'),
            array(
                'operator'      => 'AND',
                'conditions'    => array('key' => 'picture', 'selector' => $this->tUser->user['id'])));

        // Check the query
        if (!$query) {
            return alert_notify('danger', 'There was an issue when updating the picture field in the database.', '', true);
        }

        return true;
    }


    /**
     * Saves users personal information to the database
     *
     * @param array $user
     * @return string|boolean
     */
    public function save_current_personal($user) {
        // Check for a logged in user
        if (!$this->check_user()) die('You must be logged in to save personal account information.');

        // Validate the first name
        if (!isset($user['firstname']) || $user['firstname'] == '') {
            return alert_notify('danger', 'Please fill out the "First Name" field.', '', true);
        }

        // Validate the last name
        if (!isset($user['lastname']) || $user['lastname'] == '') {
            return alert_notify('danger', 'Please fill out the "Last Name" field.', '', true);
        }

        // Define the birthday
        $user['birthday'] = $user['bday_y'].'-'.$user['bday_m'].'-'.$user['bday_d'];

        // Update the database with this new information
        $query = $this->tData->update_table_row($this->tData->prefix.'users',
            array(
                array('value' => $user['firstname']),
                array('value' => $user['lastname']),
                array('value' => $user['gender']),
                array('value' => $user['birthday'])),
            array(
                array(
                    'operator'      => 'AND',
                    'conditions'    => array('key' => 'firstname', 'selector' => $this->tUser->user['id'])),
                array(
                    'operator'      => 'AND',
                    'conditions'    => array('key' => 'lastname', 'selector' => $this->tUser->user['id'])),
                array(
                    'operator'      => 'AND',
                    'conditions'    => array('key' => 'gender', 'selector' => $this->tUser->user['id'])),
                array(
                    'operator'      => 'AND',
                    'conditions'    => array('key' => 'birthday', 'selector' => $this->tUser->user['id']))));

        // Check the query
        if (!$query) {
            return alert_notify('danger', 'There was an issue when saving this information to the database.', '', true);
        }

        return true;
    }


    /**
     * Saves user contact information to the database
     *
     * @param array $user
     * @return string|boolean
     */
    public function save_current_contact($user) {
        // Check for a logged in user
        if (!$this->check_user()) die('You must be logged in to save account contact information.');

        // Check for an email address
        if (!isset($user['email']) || $user['email'] == '') {
            return alert_notify('danger', 'Please fill out the "Email Address" field.', '', true);
        }

        // Validate the email
        if ($this->define_email($user['email']) != true) {
            return alert_notify('danger', 'Please enter a valid email address.', '', true);
        }

        // Update the database with this information
        $query = $this->tData->update_table_row($this->tData->prefix.'users',
            array(
                array('value' => $user['email']),
                array('value' => $this->check_phone($user['phone']))),
            array(
                array(
                    'operator'      => 'AND',
                    'conditions'    => array('key' => 'email', 'selector' => $this->tUser->user['id'])),
                array(
                    'operator'      => 'AND',
                    'conditions'    => array('key' => 'phone', 'selector' => $this->tUser->user['id']))));

        // Check the query
        if (!$query) {
            return alert_notify('danger', 'There was an issue when saving this information to the database', '', true);
        }

        return true;
    }


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
        $selector_query = $this->tData->select_from_table($this->tData->prefix.'users', array('selector'), array(
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
        $user_query = $this->tData->select_from_table($this->tData->prefix.'users', array(), array(
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
        // Check for an administrator
        if (!$this->tUser->is_admin()) die('You must be an administrator to do this.');

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
        // Check for an administrator
        if (!$this->tUser->is_admin()) die('You must be an administrator to do this.');

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
        // Validate the given id, making sure it exists and has a value
        if (!isset($args['id']) || $args['id'] == '') {
            return 'Invalid account ID provided (or not?)';
        }

        // Return the data returned by the remove_account function from the parent class
        return $this->remove_account($args['id']);
    }
}