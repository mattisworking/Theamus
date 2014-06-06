<?php

class Accounts {
    protected $tData;
    protected $tUser;

    public $file;

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
        $this->tPages           = new tPages();
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
        $query = $this->tData->select_from_table($this->tData->prefix."_users", array(), array("operator" => "AND", "conditions" => array('key' => 'username', 'value' => $username)));

        // Check for results and return
        return $this->tData->count_rows($query) == 0 ? true : false;
    }

    protected function check_phone($number = '') {
        $phone = '';

        if ($number != '') {
            $phone = urldecode($number);
            $numbers = preg_replace('/[^0-9]/', '', $phone);        // Get rid of anything that isn't a number
            if (strlen($numbers) >= 10 && strlen($numbers) <= 11) { // If there's a leading 1
                $numbers = preg_replace('/^1/', '',$numbers);       // Remove the leading 1
            }

            if (strlen($numbers) == 10 && is_numeric($numbers)) {   // If the phone number is 10 integers
                $phone = $numbers;
            }
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
     * Activates a user in the database based on their email address and activation code
     *
     * @param string $email
     * @param string $activation_code
     * @return array
     */
    protected function activate_a_user($email, $activation_code) {
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

    public function accounts_tabs($file = '') {
        $tabs = array(
            array('List of Users', 'admin/index.php', 'Theamus Accounts'),
            array('Search Users', 'admin/search-accounts.php', 'Search Accounts'),
            array('Create a New User', 'admin/create-account.php', 'Create a New Account')
        );

        $return_tabs = array();

        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : '';
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'accounts-tab\' data-file=\'accounts/'.trim($tab[1], '.php').'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        return '<ul>'.implode('', $return_tabs).'</ul>';
    }

    protected function get_accounts() {
        $query_data = array(
            "table_name"    => $this->tData->prefix."_users",
            "clause"        => array(
                "operator"      => "",
                "conditions"    => array()
            ));

        $query = $this->tData->select_from_table($query_data['table_name'], array(), array(), 'ORDER BY `selector`');
        $user_results = $this->tData->fetch_rows($query);

        $user_data = array();
        foreach ($user_results as $user) {
            $user_data[$user['selector']][$user['key']] = $user['value'];
        }

        return $user_data;
    }

    protected function search_for_accounts($search_query = '') {
        if ($search_query == '') {
            return '';
        }

        $query_data = array(
            'table'     => $this->tData->prefix.'_users',
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

        $selector_query = $this->tData->select_from_table($query_data['table'], array('selector'), $query_data['clause']);

        if ($selector_query == false || $this->tData->count_rows($selector_query) == 0) {
            return alert_notify('info', 'No accounts were found.', '', true);
        }

        $selector_results = $this->tData->fetch_rows($selector_query);
        $selectors = isset($selector_results[0]) ? $selector_results : array($selector_results);

        $used_selectors = $users = $user_clauses = array();

        $desired_keys = array('id', 'username', 'firstname', 'lastname', 'permanent');

        foreach ($selectors as $selector) {
            if (in_array($selector['selector'], $used_selectors)) {
                continue;
            }
            
            $user_clauses[] = array('operator' => '', 'conditions' => array('selector' => $selector['selector']));
        }
        
        $user_query = $this->tData->select_from_table($query_data['table'], array('key', 'value', 'selector'), array(
            'operator'      => 'OR',
            'conditions'    => $user_clauses
        ), 'ORDER BY `id` ASC');

        if ($user_query != false) {
            $used_selectors[] = $selector;
            $user_results = $this->tData->fetch_rows($user_query);
            
            foreach ($user_results as $user_data) {
                if (in_array($user_data['key'], $desired_keys)) {
                    $users[$user_data['selector']][$user_data['key']] = $user_data['value'];
                }
            }
        }

        return $users;
    }

    protected function check_account_parameters($args, $edit = false) {
        $required = array(
            array('First Name', 'firstname'),
            array('Last Name', 'lastname'),
            array('Gender', 'gender'),
            array('Birthday Month', 'bday_month'),
            array('Birthday Day', 'bday_day'),
            array('Birthday Year', 'bday_year'),
            array('Email', 'email'),
            array('Phone', 'phone'),
            array('Groups', 'groups'),
            array('Administrator', 'is_admin')
        );

        if ($edit == true) {
            $required[] = array('Change Password', 'change_password');
        } else {
            $required[] = array('Username', 'username');
        }

        foreach ($required as $parameter) {
            if (!isset($args[$parameter[1]]) || $args[$parameter[1]] == '') {
                return alert_notify('danger', 'Please fill out the \''.$parameter[0].'\' field.', '', true);
            }
        }

        return $args;
    }

    protected function decode($str) {
        $decoded = "";
        for($i = 0; $i < strlen($str); $i++) {
            $b = ord($str[$i]);
            $a = $b ^ 123;
            $decoded .= chr($a);
        }

        return $decoded;
    }

    protected function sanitize_account_variables($data, $edit = false) {
        if ($edit == true) {
            if (!isset($data['id']) || $data['id'] == '') {
                return alert_notify('danger', 'Invalid account ID provided (or not?)', '', true);
            }
            $user_variables['id'] = Accounts::decode($data['id']);
        }

        if ($edit == false) {
            $defined_username = $this->define_username($data['username']);
            if ($defined_username !== true) {
                return alert_notify('danger', 'The username you\'ve provided is '.$defined_username.'.', '', true);
            } else {
                $user_variables['username'] = $data['username'];
            }

            $user_variables['activation-code'] = md5(time());
        }

        // Check and define the password
        if (is_bool($data['change_password'])) {
            if ($data['password'] == '') {
                return alert_notify('danger', 'Please fill out the \'Password\' field.');
            }

            if ($data['password_again'] == '') {
                return alert_notify('danger', 'Please fill out the \'Password Again\' field.');
            }

            $defined_password = $this->define_password($data['password']);
            if (!is_bool($defined_password)) {
                return alert_notify('danger', 'The password provided is too short.', '', true);
            } else {
                if ($data['password'] != $data['password_again']) {
                    return alert_notify('danger', 'The passwords provided do not match.', '', true);
                } else {
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
        $user_variables['phone'] = $this->check_phone($data['phone']);
        $user_variables['birthday'] = $data['bday_year'].'-'.$data['bday_month'].'-'.$data['bday_day'];
        $user_variables['gender'] = $data['gender'];
        $user_variables['groups'] = $data['groups'];
        $user_variables['admin'] = $data['is_admin'] != 1 ? 0 : 1;

        return $user_variables;
    }

    protected function create_account($data, $registration = false) {
        $user_variables = Accounts::sanitize_account_variables($data);

        if (is_array($user_variables) == false) {
            return $user_variables;
        }

        $selector_query = $this->tData->select_from_table($this->tData->prefix.'_users', array('selector'), array(), 'GROUP BY `selector` ORDER BY `selector` DESC LIMIT 1');
        if ($selector_query == false) {
            $selector = time();
        } else {
            $selector_data = $this->tData->fetch_rows($selector_query);
            $selector = $selector_data['selector'] + 1;
        }

        // Register the user
        $query = $this->tData->insert_table_row($this->tData->prefix.'_users', array(
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
            array('key' => 'active', 'value' => 1, 'selector' => $selector),
            array('key' => 'activation_code', 'value' => $user_variables['activation-code'], 'selector' => $selector)
        ));

        // Check the query and continue
        if (!$query) {
            return alert_notify('danger', 'There was an error registering you in the database. Please try again later.', '', true);
        } else {
            return true;
        }
    }

    public function save_account($data) {
        $user_variables = Accounts::sanitize_account_variables($data, true);

        if (is_array($user_variables) == false) {
            return $user_variables;
        }

        $query_data = array(
            'table'     => $this->tData->prefix.'_users',
            'data'      => array(
                array('value' => $user_variables['email']),
                array('value' => $user_variables['firstname']),
                array('value' => $user_variables['lastname']),
                array('value' => $user_variables['phone']),
                array('value' => $user_variables['birthday']),
                array('value' => $user_variables['gender']),
                array('value' => $user_variables['groups']),
                array('value' => $user_variables['admin'])
            ),
            'clause'    => array(
                array('operator' => 'AND', 'conditions' => array('key' => 'email', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'firstname', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'lastname', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'phone', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'birthday', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'gender', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'groups', 'selector' => $user_variables['id'])),
                array('operator' => 'AND', 'conditions' => array('key' => 'admin', 'selector' => $user_variables['id']))
            )
        );

        if (isset($user_variables['password'])) {
            $query_data['data'][] = array('value' => $user_variables['password']);
            $query_data['clause'][] = array('operator' => 'AND', 'conditions' => array('key' => 'password', 'selector' => $user_variables['id']));
        }

        $query = $this->tData->update_table_row($query_data['table'], $query_data['data'], $query_data['clause']);

        if ($query == false) {
            return alert_notify('danger', 'There was an issue with saving this information to the database.', '', true);
        }

        return true;
    }

    public function remove_account($id) {
        $query = $this->tData->delete_table_row($this->tData->prefix.'_users', array('operator' => '', 'conditions' => array('selector' => Accounts::decode($id))));

        if ($query == false) {
            return alert_notify('There was an issue removing this account from the database.');
        }

        return true;
    }
}