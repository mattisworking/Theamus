<?php

class Accounts {
    /**
     * Connect to Theamus
     *
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;
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
            throw new Exception('Invalid username.');

        // Check the username length
        } elseif (strlen($username) > 25 || strlen($username) < 4) {
            throw new Exception('Invalid username.');

        // Check for and existing username
        } elseif (!$this->check_unused_username($username)) {
            throw new Exception('Username taken.');
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
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('users'),
            array(),
            array('operator' => '',
                'conditions' => array(
                    'username' => $username)));

        // Check for results and return
        return $this->Theamus->DB->count_rows($query) == 0 ? true : false;
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
     * Turns numbers into purty phone number 8005551234 -> 1 (800) 555-1234
     *
     * @param int $number
     * @return string
     */
    public function format_phone($number) {
        $phone = '';

        if ($number != '') {
            $phone = '1 ';
            $phone .= '('.substr($number, 0, 3).')';
            $phone .= ' '.substr($number, 3, 3);
            $phone .= '-'.substr($number, 6, 10);
        }

        return $phone;
    }


    /**
     * Defines the password
     *
     * @param string $password
     * @return string
     */
    protected function define_password($password = '') {
        // Check the password length
        if (strlen($password) < 4) throw new Exception('The password must be at least 4 characters long.');
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
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Invalid email.');
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
        // Create the email message
        $activation_addy = $this->Theamus->base_url.'accounts/activate/&email='.$email.'&code='.$activation_code;
        $message = 'You\'ve recently registered to '.$this->Theamus->settings['name'].'!<br /><br />';
        $message .= 'Now all you have to do is activate your account before you can log in.<br />';
        $message .= 'To activate your new account, <a href=\''.$activation_addy.'\'>click here</a>!';

        // Send the mail
        return $this->Theamus->mail($email, 'Activate Your Account', $message);
    }


    /**
     * Activates a user in the database based on their email address and activation code
     *
     * @param string $email
     * @param string $activation_code
     * @return array
     */
    protected function activate_a_user($email, $activation_code) {
        // Try to find the user in the database
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('users'),
            array('email', 'activation_code', 'active'),
            array('operator' => 'AND',
                'conditions' => array(
                    'email'             => $email,
                    'activation_code'   => $activation_code)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('Failed to find the user.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find the user.');

        // Define the user information
        $user = $this->Theamus->DB->fetch_rows($query);

        // Check if the user is activated already or not
        if ($user['active'] == 1) throw new Exception('This account has already been activated. <a href="accounts/login/">Log in here</a>.');

        // Update the user's active status
        $update_query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('users'),
            array('active' => 1),
            array('operator' => 'AND',
                'conditions' => array(
                    'email'             => $email,
                    'activation_code'   => $activation_code)));

        // Check the update query
        if (!$update_query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('Failed to activate this account.');
        }

        return true; // Return true!
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
            array('List of Users', 'accounts/admin/index.php', 'Theamus Accounts'),
            array('Search Users', 'accounts/admin/search-accounts.php', 'Search Accounts'),
            array('Create a New User', 'accounts/admin/create-account.php', 'Create a New Account'));

        // Return the HTML tabs
        return $this->Theamus->Theme->generate_admin_tabs("accounts-tab", $tabs, $file);
    }


    /**
     * Get all of the accounts from the database
     *
     * @return array
     */
    protected function get_accounts() {
        // Query the database for all of the users
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('users'),
            array('id', 'username', 'firstname', 'lastname', 'permanent'));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to get all user accounts.');
        }

        // Define the information
        $results = $this->Theamus->DB->fetch_rows($query);

        // Return the information
        return isset($results[0]) ? $results : array($results);
    }


    /**
     * Searches the database for users based on their username, firstname or lastname
     *
     * @param string $search_query
     * @return string|array $users
     */
    protected function search_for_accounts($search_query = '') {
        // Check the search query and return if it is empty
        if ($search_query == '') return array();

        // Query the database for results
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('users'),
            array('id', 'username', 'firstname', 'lastname', 'permanent'),
            array('operator' => 'OR',
                'conditions' => array(
                    '[%]username'  => $search_query.'%',
                    '[%]firstname' => $search_query.'%',
                    '[%]lastname'  => $search_query.'%')));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('Failed to search for accounts.');
        }

        // Fetch the db information
        $results = $this->Theamus->DB->fetch_rows($query);

        // Return the user accounts
        return isset($results[0]) ? $results : array($results);
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
                throw new Exception('Please fill out the \''.$parameter[0].'\' field.');
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
                throw new Exception('Please fill out the \''.$parameter[0].'\' field.');
            }
        }

        // Return the arguments
        return $args;
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
            if (!isset($data['id']) || $data['id'] == '') throw new Exception('Invalid account ID provided (or not?)');

            // Define the user ID in the return array
            $user_variables['id'] = $data['id'];

            // Define the user active variable
            $user_variables['active'] = !isset($data['active']) || $data['active'] == '' || is_bool($data['active']) ? 1 : 0;
        }

        // Check the request - conting on if it IS an 'edit' request'
        if ($edit == false) {
            // Check and define the username
            $defined_username = $this->define_username($data['username']);

            // Check the check and return or define the username
            if ($defined_username) $user_variables['username'] = $data['username'];

            // Define the activation code
            $user_variables['activation-code'] = md5(time());
        }

        // Check and define the password
        if (is_bool($data['change_password'])) {
            // Check for a password
            if ($data['password'] == '') throw new Exception('Please fill out the \'Password\' field.');

            // Check for the repeat password
            if ($data['password_again'] == '') throw new Exception('Please fill out the \'Password Again\' field.');

            // Check the password
            $defined_password = $this->define_password($data['password']);

            // Check the check and return if it is a bad password
            if ($defined_password === true) {
                // Check the password against the repeated password and return
                if ($data['password'] != $data['password_again']) {
                    throw new Exception('The passwords provided do not match.');
                } else {
                    // Define the encrypted password
                    $salt = $this->Theamus->DB->get_config_salt('password');
                    $user_variables['password'] = hash('SHA256', $data['password'].$salt);
                }
            }
        }

        // Check and define the email address
        if ($this->define_email($data['email']) === true) $user_variables['email'] = $data['email'];

        // Check and define the first name
        if (strlen($data['firstname']) > 50) {
            throw new Exception('The first name is too long.  Use a nick name.');
        } else {
            $user_variables['firstname'] = $data['firstname'];
        }

        // Check and define the last name
        if (strlen($data['lastname']) > 125) {
            throw new Exception('The last name is too long.');
        } else {
            $user_variables['lastname'] = $data['lastname'];
        }

        // Define all other user registration information
        $user_variables['phone']    = $registration == false ? $this->check_phone($data['phone']) : '';
        $user_variables['birthday'] = $registration == false ? $data['bday_year'].'-'.$data['bday_month'].'-'.$data['bday_day'] : date('Y-m-d');
        $user_variables['gender']   = $registration == false ? $data['gender'] : 'm';
        $user_variables['groups']   = $registration == false ? $data['groups'] : 'everyone,basic_users';
        $user_variables['admin']    = 0;
        if ($registration == false) $user_variables['admin'] = $data['is_admin'] != 1 ? 0 : 1;

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
        if (!$registration && (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('add_users'))) {
            die('You must be an administrator to do this.');
        }

        // Sanitize and check the given variables
        $user_variables = $this->sanitize_account_variables($data, false, $registration);

        // Check the user variables and return the error the check gave, if there is one
        if (is_array($user_variables) == false) return $user_variables;

        // Create the user in the database
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);
        $query = $this->Theamus->DB->insert_table_row(
            $this->Theamus->DB->system_table('users'), array(
                array('username'        => $user_variables['username'],
                    'password'          => $user_variables['password'],
                    'email'             => $user_variables['email'],
                    'firstname'         => $user_variables['firstname'],
                    'lastname'          => $user_variables['lastname'],
                    'birthday'          => $user_variables['birthday'],
                    'gender'            => $user_variables['gender'],
                    'admin'             => $user_variables['admin'],
                    'groups'            => $user_variables['groups'],
                    'permanent'         => 0,
                    'phone'             => $user_variables['phone'],
                    'picture'           => 'default-user-picture.png',
                    'created'           => '[func]now()',
                    'active'            => !$registration || $this->Theamus->settings['email_host'] == '' ? 1 : 0,
                    'activation_code'   => $user_variables['activation-code'])));

        // Check the query for errors
        if (!$query) {
            // Rollback the new rows
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('There was an error registering you in the database. Please try again later.');
        }

        // Check if it's a registered user and there is an email host defined
        if ($registration && $this->Theamus->settings['email_host'] != '') {
            // Try to email the new user their activation code
            if (!$this->email_registered_user($user_variables['email'], $user_variables['activation-code'])) {
                // Rollback the new user rows
                $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

                // Notify the user
                throw new Exception('There was an error registering you. Please try again later.');
            }

            // Commit the new user to the database and return positively
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();

            return true; // Return true!
        } else if ($registration && $this->Theamus->settings['email_host'] == '') {
            // Commit the new user to the database and return positively
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();

            return 'You have been registered successfully.  <a href="accounts/login/">Click here to log in</a>';
        } else if (!$registration) {
            // Commit the new user to the database and return positively
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();

            return true; // Return true!
        }

        // Default error
        throw new Exception('Failed to create a new user');
    }


    /**
     * Saves edited user account information in the database
     *
     * @param array $data
     * @return string|boolean
     */
    public function save_account($data) {
        // Check for an administrator
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('edit_users')) die('You must be an administrator to do this.');

        // Sanitize and check the given variables
        $user_variables = $this->sanitize_account_variables($data, true);


        // Define the query data that will properly update the user information
        $query_data = array(
            'email'     => $user_variables['email'],
            'firstname' => $user_variables['firstname'],
            'lastname'  => $user_variables['lastname'],
            'phone'     => $user_variables['phone'],
            'birthday'  => $user_variables['birthday'],
            'gender'    => $user_variables['gender'],
            'groups'    => $user_variables['groups'],
            'admin'     => $user_variables['admin'],
            'active'    => $user_variables['active']);

        // Check if the user is changing their password and add it to the update query data
        if (isset($user_variables['password'])) $query_data['password'] = $user_variables['password'];

        // Query the database, updating the information provided
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('users'),
            $query_data,
            array('operator' => '',
                'conditions' => array('id' => $user_variables['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('There was an issue with saving this information to the database.');
        }

        return true; // Return true!
    }


    /**
     * Removes a user from the database
     *
     * @param string $id
     * @return string|boolean
     */
    public function remove_account($id) {
        // Check for an administrator
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('remove_users')) die('You must be an administrator to do this.');

        // Check the given ID and return respectively
        if ($id == '') throw new Exception('The given user id is invalid.', '', true);

        // Remove the user's information from the database
        $query = $this->Theamus->DB->delete_table_row(
            $this->Theamus->DB->system_table('users'),
            array('operator' => '',
                'conditions' => array('id' => $id)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('Failed to delete this account.');
        }

        return true; // Return true!
    }


    /**
     * Checks for a logged in user
     *
     * @return boolean
     */
    private function check_user() {
        return $this->Theamus->User->user != false ? true : false;
    }


    /**
     * Saves the currently logged in user's edited information to the database
     *
     * @param array $user
     * @return string|boolean
     */
    public function save_current_account($user) {
        // Boot the user if they aren't logged in
        if (!$this->check_user()) throw new Exception('You must be logged in to save account information.');

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
                throw new Exception('Invalid type of profile picture uploaded.');
            }

            // Upload the file
            if (move_uploaded_file($picture['tmp_name'], $this->Theamus->file_path(ROOT.'/media/profiles/'.$filename))) {
                $query_data['data'][]   = array('picture' => $filename);
                $query_data['clause'][] = array(
                    'operator'   => 'AND',
                    'conditions' => array(
                        'id' => $this->Theamus->User->user['id']));
            } else {
                throw new Exception('There was an error uploading your picture. This may be because of file permissions.');
            }
        }

        // Save the user's password
        if (is_bool($user['change_password'])) {
            // Check for a password
            if (!isset($user['password']) || $user['password'] == '') throw new Exception('Please fill out the password field.');

            // Check for a repeated password
            if (!isset($user['password_again']) || $user['password_again'] == '') throw new Exception('Please fill out the password field.');

            // Check the password length
            if ($this->define_password($user['password']) !== true) throw new Exception('The password provided is too short.');

            // Check the passwords against eachother
            if ($user['password'] != $user['password_again']) throw new Exception('The passwords provided do not match.');

            // Define the salt to encrypt the password with
            $salt = $this->Theamus->DB->get_config_salt('password');

            // Update the database
            $query_data['data'][] = array('password' => hash('SHA256', $user['password'].$salt));
            $query_data['clause'][] = array(
                    'operator'   => 'AND',
                    'conditions' => array(
                        'id' => $this->Theamus->User->user['id']));
        }

        // Update the database
        if (isset($query_data['data']) && isset($query_data['clause'])) {
            $update_query = $this->Theamus->DB->update_table_row(
                $this->Theamus->DB->system_table('users'),
                $query_data['data'],
                $query_data['clause']);

            // Check the update query for errors
            if(!$update_query) {
                $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

                // Notify the user
                throw new Exception('Failed to save account information.');
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
        if ($this->Theamus->User->user['picture'] == 'default-user-picture.png') {
            throw new Exception('You cannot remove the default profile picture.');
        }

        // Remove the user's picture from the folder
        if (!unlink($this->Theamus->file_path(ROOT.'/media/profiles/'.$this->Theamus->User->user['picture']))) {
            throw new Exception('Failed to delete the picture.');
        }

        // Update the table row to reflect the change
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('users'),
            array('picture' => 'default-user-picture.png'),
            array('operator' => 'AND',
                'conditions' => array('id' => $this->Theamus->User->user['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the last error

            // Notify the user
            throw new Exception('Failed to change the picture back to the default.');
        }

        return true; // Return true!
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
        if (!isset($user['firstname']) || $user['firstname'] == '') throw new Exception('Please fill out the "First Name" field.');

        // Validate the last name
        if (!isset($user['lastname']) || $user['lastname'] == '') throw new Exception('Please fill out the "Last Name" field.');

        // Define the birthday
        $user['birthday'] = $user['bday_y'].'-'.$user['bday_m'].'-'.$user['bday_d'];

        // Update the database with this new information
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('users'),
            array('firstname' => $user['firstname'],
                'lastname'    => $user['lastname'],
                'gender'      => $user['gender'],
                'birthday'    => $user['birthday']),
            array('operator' => '',
                'conditions' => array('id' => $this->Theamus->User->user['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('Failed to save personal information.');
        }

        return true; // Return true!
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
        if (!isset($user['email']) || $user['email'] == '') throw new Exception('Please fill out the "Email Address" field.');

        // Validate the email
        if ($this->define_email($user['email']) != true) throw new Exception('Please enter a valid email address.');

        // Update the database with this information
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('users'),
            array(
                'email' => $user['email'],
                'phone' => $this->check_phone($user['phone'])),
            array('operator' => '',
                'conditions' => array('id' => $this->Theamus->User->user['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('Failed to save contact information.');
        }

        return true;
    }


    /**
     * Logs a user out, destroying their session and cookies
     *
     * @return boolean
     */
    public function logout() {
        return $this->Theamus->User->force_logout();
    }


    /**
     * Logs a user in, creating a session and setting cookies
     *
     * @param array $args
     * @return array|boolean
     */
    public function login($args) {
        // Define the configured salts
        $session_salt = $this->Theamus->DB->get_config_salt('session');

        // Validate the username
        if (isset($args['username'])) {
            if ($args['username'] == '') throw new Exception('Please fill out the \'Username\' field.');
        // No username variable was found
        } else throw new Exception('There was an error finding the username variable.');

        // Validate the password
        if (isset($args['password'])) {
            if ($args['password'] == '') throw new Exception('Please fill out the \'Password\' field.');
        // No password was variable found
        } else throw new Exception('There was an error finding the password variable.');

        // Define the username and hashed password
        $username = urldecode($args['username']);
        $password = hash('SHA256', urldecode($args['password']).$this->Theamus->DB->get_config_salt('password'));

        // Query the database for this user
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('users'),
            array('id', 'username', 'password', 'active'),
            array('operator' => 'AND',
                'conditions' => array(
                    'username' => $username,
                    'password' => $password)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error in the database
            throw new Exception('Failed to find the user.'); // Notify the user
        }

        // Check for query results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Invalid credentials.');

        // Define the selectors related to the provided username
        $user = $this->Theamus->DB->fetch_rows($query);

        // Check the user's active status
        if ($user['active'] == 0) throw new Exception('This account is not active.');

        // Define a new session value and the cookie expiration time
        $session = md5(time().$session_salt);
        $expire  = time() + 3600;

        // Define the expire time to be two weeks from now
        if (isset($args['keep_session']) && $args['keep_session'] == true) $expire = time() + (60 * 60 * 24 * 14);

        // Update the user's session in the database
        if (!$this->Theamus->User->add_user_session($user['id'], $session, $expire)) {
            throw new Exception('There was an error updating/creating the session.');
        }

        return true; // Return true!
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
        if (!isset($args['username']) || $args['username'] == '') throw new Exception('Invalid username.');

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
        if (!isset($args['password']) || $args['password'] == '') throw new Exception('Invalid password.');

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
        if (!isset($args['email']) || $args['email'] == '') throw new Exception('Invalid email.');

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
        if (!is_bool($data) && !is_array($data)) return $data;

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
    public function activate_user($email, $code) {
        // Validate the given email address, making sure it exists and it isn't empty
        if ($email == '') throw new Exception('Couldn\'t activate because there is no email address defined.');

        // Validate the activation code, making sure it exists and it isn't empty
        if ($code == '') throw new Exception('Couldn\'t activation because there is no activation code defined.');

        // Define the email address and activation code
        $email = urldecode($email);
        $code = urldecode($code);

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
            $this->Theamus->User->has_permission('edit_users') ? '<li><a href=\'#\' name=\'edit-account-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
            $this->Theamus->User->has_permission('remove_users') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-account-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
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
        if (!$this->Theamus->User->is_admin()) die('You must be an administrator to do this.');

        // Define the page data information that will set up the return data
        $this->Theamus->Pagination->set_page_data(array(
            'data'              => $this->get_accounts(),
            'per_page'      	=> 15,
            'current'       	=> $args['page'],
            'list_template' 	=> $this->user_template()
        ));

        // Return the list of users
        return '<ul class=\'accounts\'>'.$this->Theamus->Pagination->print_list(true).'</ul>'.$this->Theamus->Pagination->print_pagination('accounts_next_page', 'admin-pagination', true);
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
        if (!$this->Theamus->User->is_admin()) die('You must be an administrator to do this.');

        // Validate the search query, making sure it exists
        if (!isset($args['search_query'])) throw new Exception('The search query was not found.');

        // Define the current page number
        $args['page'] = !isset($args['page']) || !is_numeric($args['page']) ? 1 : $args['page'];

        // Search for the accounts in the database
        $searched_accounts = $this->search_for_accounts($args['search_query']);

        // Check if there are users to show - if not, show the results
        if (!is_array($searched_accounts)) return $searched_accounts;

        // Define the page data information that will set up the return data
        $this->Theamus->Pagination->set_page_data(array(
            'data'              => $searched_accounts,
            'per_page'      	=> 15,
            'current'       	=> $args['page'],
            'list_template' 	=> $this->user_template()
        ));

        // Return the list of users
        return '<ul class=\'accounts\'>'.$this->Theamus->Pagination->print_list(true).'</ul>'.$this->Theamus->Pagination->print_pagination('search_accounts_next_page', 'admin-pagination', true);
    }


    /**
     * Calls a parent(Accounts) class function that creates a new user in the database
     *
     * @param array $args
     * @return string|boolean
     */
    public function create_new_account($args) {
        // Validate the fields required to create a new account
        $this->check_account_parameters($args);

        $args['change_password'] = true; // Change the password (create)

        // Return the data returned by creating an account from the parent class
        return $this->create_account($args);
    }


    /**
     * Calls a parent(Accounts) class function that saves account information to the database
     *
     * @param array $args
     * @return string|boolean
     */
    public function save_account_information($args) {
        // Validate the fields required to save account information
        $this->check_account_parameters($args, true);

        // Return the data returned by saving the account information function from the parent class
        return $this->save_account($args);
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
    
    
    /**
     * Gets user information from the database based on a user's username and email address
     * 
     * @param string $username
     * @param string $email
     * @throws Exception
     * @return array
     */
    private function get_user($username, $email) {
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table("users"),
            array("id", "username", "email", "admin", "activation_code"),
            array("operator" => "&&",
                "conditions" => array("username" => $username, "email" => $email)));
                
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to find the user in the database right now. Please try again later.");
        }
        
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception("No user was found matching the information provided.");
        
        return $this->Theamus->DB->fetch_rows($query);
    }
    
    
    /**
     * Sends a password reset code to the user. Will default to creating a file 
     * with the reset code in it if no email server has been set up.
     * 
     * @param array $args
     * @throws Exception
     * @return array
     */
    public function send_password_reset($args = array()) {
        // Validate user and given variables
        if ($this->Theamus->User->user) throw new Exception("You're already logged in!");
        if (!isset($args['username']) || $args['username'] == "") throw new Exception("Please give a username to look for.");
        if (!isset($args['email']) || $args['email'] == "") throw new Exception("Please give an email to send to. The username must have this email address associated to it.");

        // Find the user in the database        
        $user = $this->get_user($args['username'], $args['email']);
        
        // Genertate a reset code based on the time
        $reset_code = md5(microtime(true));

        // Update the users "activation code" with the password reset code so we
        // can check against it later when they reset their password.
        $record_code_query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table("users"),
            array("activation_code" => $reset_code),
            array("operator" => "&&",
                "conditions" => array("username" => $args['username'], "email" => $args['email'])));
                
        // Check the update query for errors
        if (!$record_code_query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to record the reset code on our end. Please try again later.");
        }
        
        // If the website has email settings set up
        if ($this->Theamus->settings['email_host'] == "") {
            // Only administrators can get a reset code file made for them
            if ($user['admin'] == "0") {
                throw new Exception("Cannot email your password reset code.");
            } else {
                // Create the file with the code in it and return OK
                $this->create_reset_code_file($reset_code);
                return array("message" => "A password reset code file has been created for you to use.",
                    "from_file" => 1,
                    "username" => $user['username'],
                    "email" => $user['email']);
            }
            
        // Email the user the reset code
        } else {
            $message = "Your password reset code is: <strong>{$reset_code}</strong>";
            // Try to send out the email
            if (!$this->Theamus->mail($user['email'], "Password Reset Code", $message)) {
                throw new Exception("Failed to send out your password reset code. Please try again later.");
            } else {
                return array("message" => "A password reset code has been emailed to you.",
                    "from_file" => 0,
                    "username" => $user['username'],
                    "email" => $user['email']);
            }
        }
    }
    
    
    /**
     * Creates a file in the root of the Theamus installation folder that holds
     * the contents of a password reset code.
     * 
     * @param string $reset_code
     * @throws Exception
     */
    private function create_reset_code_file($reset_code) {
        $file = $this->Theamus->file_path(ROOT."/password-reset-code.txt");
        if (file_put_contents($file, $reset_code) === false) {
            $this->Theamus->Log->system("Failed to create the password reset code file. Probably because of file permissions.");
            throw new Exception("Failed to create the password reset code file.");
        }
    }
    
    
    /**
     * Resets the user's password with the requested reset code
     * 
     * @param array $args
     * @throws Exception
     * @returns boolean
     */
    public function reset_password($args = array()) {
        // Check the user and the given variables
        if ($this->Theamus->User->user) throw new Exception("You're already logged in!");
        if (!isset($args['username']) || $args['username'] == "") throw new Exception("Please give a username to look for.");
        if (!isset($args['email']) || $args['email'] == "") throw new Exception("Please give an email to send to. The username must have this email address associated to it.");

        // Verify the user exists in the database        
        $user = $this->get_user($args['username'], $args['email']);
        
        // Check more variables
        $file = $this->Theamus->file_path(ROOT."/password-reset-code.txt");
        if (!isset($args['from_file']) || !is_numeric($args['from_file'])) $args['from_file'] = 0;
        if (!isset($args['reset_code']) || $args['reset_code'] == "") throw new Exception("Please give the reset code you received.");
        if (!isset($args['password']) || $args['password'] == "") throw new Exception("Please fill out the 'New Password' field.");
        if (!isset($args['repeat_password'])) throw new Exception("Please fill out the 'Repeat New Password' field.");
        if ($args['repeat_password'] != $args['password']) throw new Exception("The passwords given don't match up.");

        // Check the given reset code against the one in the database
        if ($args['from_file'] == 1) {
            if ($args['reset_code'] != file_get_contents($file)) throw new Exception("The reset code you have doesn't matche the one in the file.");
        } else {
            if ($args['reset_code'] != $user['activation_code']) throw new Exception("The reset code you have doesn't match the one we have.");
        }
        
        // Update the user's password
        $password = hash('SHA256', urldecode($args['password']).$this->Theamus->DB->get_config_salt('password'));
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table("users"),
            array("password" => $password),
            array("operator" => "&&",
                "conditions" => array("id" => $user['id'])));

        // Check the update query
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to reset your password. Please try again later.");
        }
        
        // Delete the reset code file, if it exists.
        if (file_exists($file)) unlink($file);
        
        return true;
    }
}