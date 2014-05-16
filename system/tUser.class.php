<?php

/**
 * tUser - Theamus user information class
 * PHP Version 5.5.3
 * Version 1.2
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Eyrah Temet (Eyraahh) <info@theamus.com>
 */

class tUser {
    /**
     * Holds the mysqli object
     *
     * @var object $tData
     */
    private $tData;


    /**
     * Holds information about the currently logged in user
     *
     * @var boolean|array $user
     */
    public $user;


    /**
     * Holds the cookies provided by the browser
     *
     * @var array $cookies
     */
    private $cookies;


    /**
     * Constructs the class, initializing class variables and defining
     *  user information
     *
     * @return boolean
     */
    public function __construct() {
        $this->initialize_variables();
        $this->get_user_info();
        return true;
    }


    /**
     * Deconstructs the class, disconnecting from the database
     *
     * @return boolean
     */
    public function __destruct() {
        $this->tData->disconnect();
        return true;
    }


    /**
     * Defines variables that will be used within the class
     *
     * @return boolean
     */
    private function initialize_variables() {
        $this->cookies          = filter_input_array(INPUT_COOKIE);
        $this->tData            = new tData();
        $this->tData->db        = $this->tData->connect(true);
        $this->tData->prefix    = $this->tData->get_system_prefix();
        $this->tCall            = new tCall(false);
        return true;
    }


    /**
     * In an attempt to stop people from requesting things they shouldn't be,
     *  this function will set a hash to a user and only allow this user to make calls.
     *
     * If the cookie doesn't exist or match, they can't do anything.
     */
    public function set_420hash() {
        // Define the hash
        $hash = $this->tData->get_hash(true);

        // Set the hash cookie
        $cookie_hash = isset($_COOKIE['420hash']) ? $_COOKIE['420hash'] : false;
        if ($cookie_hash == false || $cookie_hash != $hash) {
            setcookie("420hash", $hash, (time()+60*60*24), "/");
        }
    }


    /**
     * Performs a check to see if a user is logged in
     *
     * @return boolean
     */
    private function check_login() {
        if (isset($this->cookies['session']) && isset($this->cookies['userid']))
            return true;
        return false;
    }


    /**
     * Gets all of the database information related to the user being logged in.
     *  If there is no user logged in, it will return false
     *
     * @return boolean|array $this->user
     */
    private function get_user_info() {
        if ($this->check_login()) {
            // Get the user's information from the database
            $query = $this->tData->select_from_table($this->tData->prefix."_users", array(), array("operator" => "", "conditions" => array("id" => $this->cookies['userid'])));
            $this->user = $query == false ? false : $this->tData->fetch_rows($query);

            // Get the user's session and IP address
            $user_sessions = $this->get_user_sessions(false, $this->user['id']);
            $user_ip = $_SERVER['REMOTE_ADDR'];

            // Force a logout and go to the default page if the user isn't logged in
            if (!isset($user_sessions[$user_ip]) || empty($user_sessions[$user_ip])) {
                $this->force_logout();
            } elseif ($user_sessions[$user_ip]['session_key'] != $_COOKIE['session']) {
                $this->force_logout();
            }
            return $this->user;
        }
        return false;
    }


    /**
     * Gets information specific to a user, from the provided ID
     *
     * @param int $id
     * @return boolean|array
     */
    public function get_specific_user($id = 0) {
        $q = $this->tData->select_from_table($this->tData->prefix."_users", array(), array("operator" => "", "conditions" => array("id" => $id)));
        if ($this->tData->count_rows($q) > 0) return $this->tData->fetch_rows($q);
        return false;
    }


    /**
     * Defines the user's groups from the given user database information
     *  (e.g. $this->user)
     *
     * @param array $data
     * @return array
     */
    public function get_user_groups($data) {
        if ($data == false) return array();
        return explode(",", $data['groups']);
    }


    /**
     * Performs a check to see if the currently logged in user is an administrator
     *
     * @return boolean
     */
    public function is_admin() {
        if ($this->user['admin'] == 0) return false;
        return true;
    }


    /**
     * Performs a check to see if a user is in a group
     *
     * @param string $group
     * @return boolean
     */
    public function in_group($group) {
        if ($group == "everyone") return true;
        if (!in_array($group, explode(",", $this->user['groups']))) return false;
        return true;
    }


    /**
     * Performs a check to see if a user has permission to do something
     *
     * @param string $permission
     * @return boolean
     */
    public function has_permission($permission) {
        $ret = array();
        foreach(explode(",", $this->user['groups']) as $group) {
            $q = $this->tData->select_from_table($this->tData->prefix."_groups", array(), array("operator" => "", "conditions" => array("alias" => $group)));
            $qd = $q == false ? false : $this->tData->fetch_rows($q);
            if ($qd != false) {
                $permissions = explode(",", $qd['permissions']);
                if (in_array($permission, $permissions)) $ret[] = "true";
            }
        }
        if (in_array("true", $ret)) return true;
        return false;
    }


    /**
     * Destroys a user's session, forcing them to re-login
     *
     * @return boolean
     */
    private function force_logout() {
        if (session_id() == "") {
            session_start();
        }

        session_destroy();
        setcookie("session", null, -1, "/");
        setcookie("userid", null, -1, "/");
        header("Location: ".base_url);

        return true;
    }


    /**
     * Performs a check on a user's permissions.  Dies and notifies if they do not
     *  have permission
     *
     * @param string $permission
     * @return die
     */
    public function check_permissions($permission) {
        if (!$this->has_permission($permission))
            return die(notify("admin", "failure", "You don't have permission to do this."));
    }


    /**
     * Denies any non-amdinistrator users from seeing a specific file
     *
     * @param string $current
     * @param array $files
     * @return boolean
     */
    public function deny_non_admins($current, $files) {
        if (in_array($current, $files) && !$this->is_admin()) return back_up();
        return false;
    }


    /**
     * Gets all of the sessions that are associated with the user, filters them
     *  by expiration date (not showing the old ones).  You have the option to show all of
     *  them by setting $all_sessions to true. You can also find sessions of a specific user
     *  by giving the ID to that user with the $user_id variable
     *
     * @param int $user_id
     * @param boolean $all_sessions
     * @return array
     */
    public function get_user_sessions($all_sessions = false, $user_id = 0) {
        // Check and return the user
        if ($this->user == false && $user_id == 0) {
            return array();
        }

        if ($user_id == 0) {
            $user_id = $this->user['id'];
        }

        // Query the database for the current users IP address and check it
        $query = $this->tData->select_from_table($this->tData->prefix."_user-sessions", array(), array("operator" => "", "conditions" => array("user_id" => $user_id)));
        if (!$query) {
            return array();
        }

        // Define the user's IP addresses
        $ip_addresses = array(); // blank for default
        if ($this->tData->count_rows($query) > 0) {
            $user = $this->tData->fetch_rows($query);
            $user = !isset($user[0]) ? array($user) : $user;
            foreach ($user as $u) {
                $ip_addresses[] = $u['ip_address'];
            }
        }

        // Check and return the IP addresses
        if (empty($ip_addresses)) {
            return array();
        }

        // Define the return, temp and the ignore key array
        $return = array("user_id" => $user_id);
        $temp = array();
        $i = 0;

        // Loop through all of the IP addresses, gathering information
        foreach ($ip_addresses as $address) {
            // Query the database for information related to this IP address and check it
            $query = $this->tData->select_from_table($this->tData->prefix."_user-sessions", array(), array(
                "operator"      => "AND",
                "conditions"    => array(
                    "ip_address"    => $address,
                    "user_id"       => $user_id
                )
            ));

            // Grab the information related to the IP
            $user_rows = $this->tData->fetch_rows($query);
            foreach ($user_rows as $row) {
                $temp[$address][$row['key']] = $row['value'];
                $temp[$address]['ip_address'] = $address;
            }

            $i++; // count!
        }

        // Filter out any unwanted sessions
        foreach ($temp as $item) {
            if (isset($item['expires'])) {
                // Define the ip address and remove it from the return array
                $ip = $item['ip_address'];
                unset($item['ip_address']);

                if ($all_sessions == true) {
                    $return[$ip] = $item;
                } elseif ($item['expires'] > time()) {
                    // Add the data to the return
                    $return[$ip] = $item;
                } else {
                    // define a blank array to return
                    $return[$ip] = array();
                }
            }
        }

        // Return the information
        return $return;
    }


    /**
     * Updates the user's database session information
     *
     * @param int $user_id
     * @param string $session_key
     * @param int $expire
     * @param string $ip
     * @return boolean
     */
    public function update_user_session($user_id = 0, $session_key = "", $expire = 0, $ip = "") {
        // Get the session for this user
        $user_sessions = $this->get_user_sessions(true, $user_id);
        $session = $user_sessions[$ip];

        // Get the user browser information
        $browser = $this->tCall->get_browser();
        $user_browser   = $browser['name']." ".$browser['version'];

        // Define the update queries
        $query_data['data'] = array(
            array("value" => $session_key),
            array("value" => $expire),
            array("value" => time()),
            array("value" => $user_browser)
        );
        $query_data['clause'] = array(
            array(
                "operator"      => "AND",
                "conditions"    => array(
                    "key"       => "session_key",
                    "value"     => $session['session_key'],
                    "ip_address"=> $ip,
                    "user_id"   => $user_id
                )
            ),
            array(
                "operator"      => "AND",
                "conditions"    => array(
                    "key"       => "expires",
                    "value"     => $session['expires'],
                    "ip_address"=> $ip,
                    "user_id"   => $user_id
                )
            ),
            array(
                "operator"      => "AND",
                "conditions"    => array(
                    "key"       => "last_seen",
                    "value"     => $session['last_seen'],
                    "ip_address"=> $ip,
                    "user_id"   => $user_id
                )
            ),
            array(
                "operator"      => "AND",
                "conditions"    => array(
                    "key"       => "browser",
                    "value"     => $session['browser'],
                    "ip_address"=> $ip,
                    "user_id"   => $user_id
                )
            )
        );

        // Query the database updating the user session information
        if ($this->tData->update_table_row($this->tData->prefix."_user-sessions", $query_data['data'], $query_data['clause']) != false) {
            $this->set_cookies($user_id, $session_key, $expire);
            return true;
        }
        return false;
    }


    /**
     * Define the session cookies for the user
     *
     * @param int $user_id
     * @param string $session_key
     * @param int $expire
     * @return boolean
     */
    private function set_cookies($user_id = 0, $session_key = "", $expire = 0) {
        // Return false if there are defaults
        if ($user_id == 0 || $session_key == "" || $expire == 0) {
            return false;
        }

        // Set the cookie for the user id and session id
        setcookie("userid", $user_id, $expire, "/");
        setcookie("session", $session_key, $expire, "/");
    }


    /**
     * Creates a user session in the database
     *
     * @param int $user_id
     * @param string $session_key
     * @param int $expire
     * @return boolean
     */
    public function add_user_session($user_id = 0, $session_key = "", $expire = 0) {
        // Return false if there are defaults
        if ($user_id == 0 || $session_key == "" || $expire == 0) {
            return false;
        }

        // Get the user browser information
        $browser = $this->tCall->get_browser();

        // Define SQL friendly variables
        $ip             = $_SERVER['REMOTE_ADDR'];
        $user_browser   = $browser['name']." ".$browser['version'];

        // Check if the user already has a session on this computer, it's just expired
        $user_sessions = $this->get_user_sessions(true, $user_id);
        if (isset($user_sessions[$ip])) {
            if ($user_sessions[$ip]['expires'] > time()) {
                // Define the session key, set the cookies and return
                $session_key = $user_sessions[$ip]['session_key'];
                $this->set_cookies($user_id, $session_key, $expire);

                return true;
            } else {
                // Update the user session information and return
                return $this->update_user_session($user_id, $session_key, $expire, $ip);
            }
        }

        // Define the query to add to the database
        $query = $this->tData->insert_table_row($this->tData->prefix."_user-sessions", array(
            array(
                "key"           => "session_key",
                "value"         => $session_key,
                "ip_address"    => $ip,
                "user_id"       => $user_id
            ),
            array(
                "key"           => "expires",
                "value"         => $expire,
                "ip_address"    => $ip,
                "user_id"       => $user_id
            ),
            array(
                "key"           => "last_seen",
                "value"         => time(),
                "ip_address"    => $ip,
                "user_id"       => $user_id
            ),
            array(
                "key"           => "browser",
                "value"         => $user_browser,
                "ip_address"    => $ip,
                "user_id"       => $user_id
            )
        ));

        if ($query) {
            // Set the cookies
            $this->set_cookies($user_id, $session_key, $expire);

            return true;
        }
        return false;
    }
}