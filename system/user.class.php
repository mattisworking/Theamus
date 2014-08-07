<?php

/**
 * User - Theamus user information class
 * PHP Version 5.5.3
 * Version 1.3
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Eyrah Temet (Eyraahh) <info@theamus.com>
 */

class User {

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
    public function __construct($t) {
        $this->Theamus = $t; // Make other Theamus classes usable

        // Define cookies!
        $this->cookies = filter_input_array(INPUT_COOKIE);

        $this->get_user_info();
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
        $hash = $this->Theamus->API->get_hash(true);

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
            $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table("users"),
                array(),
                array("operator" => "",
                    "conditions" => array("id" => $this->cookies['userid'])));

            if ($query == false) {
                $this->user = false;
                return false;
            }

            $this->user = $this->Theamus->DB->fetch_rows($query);

            $this->get_user_sessions();

            // Get the user's session and IP address
            $user_ip = $_SERVER['REMOTE_ADDR'];

            // Force a logout and go to the default page if the user isn't logged in
            $logout = array();
            foreach ($this->user_sessions as $user_session) {
                if (!isset($user_session['ip_address'])) continue;
                if ($user_session['ip_address'] == $user_ip && $user_session['session_key'] == $_COOKIE['session']) $logout[] = false;
            }

            if (!in_array(false, $logout)) $this->force_logout();
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
        $q = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table("users"), array(), array("operator" => "", "conditions" => array("id" => $id)));
        if ($this->Theamus->DB->count_rows($q) > 0) return $this->Theamus->DB->fetch_rows($q);
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
            $q = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table("groups"),
                array(),
                array("operator" => "",
                    "conditions" => array("alias" => $group)));

            $qd = $q == false ? false : $this->Theamus->DB->fetch_rows($q);

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
        if (session_id() == "") session_start();

        session_destroy();
        setcookie("session", null, -1, "/");
        setcookie("userid", null, -1, "/");
        header("Location: ./");

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
        if (in_array($current, $files) && !$this->is_admin()) return $this->Theamus->back_up();
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
        $user_sessions = array();

        // Check and return the user
        if ($this->user == false && $user_id == 0) {
            $this->user_sessions = $user_sessions;
            return $user_sessions;
        }

        // Define the user id to look for sessions with
        if ($user_id == 0) $user_id = $this->user['id'];

        // Query the database for the current users IP address and check it
        if (!$all_sessions) {
            $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('user-sessions'),
                array(),
                array('operator' => '',
                    'conditions' => array(
                        'user_id'   => $user_id)));
        } else {
            $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('user-sessions'));
        }

        // Check if the query failes
        if (!$query) {
            echo 3;
            $this->user_sessions = $user_sessions;
            return $user_sessions;
        }

        // Define the sessions related to this user
        $session_results = $this->Theamus->DB->fetch_rows($query);
        $user_sessions = isset($session_results[0]) ? $session_results : array($session_results);

        // Return the information
        $this->user_sessions = $user_sessions;
        return $user_sessions;
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
    public function update_user_session($user_id = 0, $session_key = '', $expire = 0, $ip = '', $session = array()) {
        // Get the user browser information
        $browser = $this->Theamus->Call->get_browser();
        $user_browser   = $browser['name']." ".$browser['version'];

        // Define the update queries
        $query_data['data'] = array(
            array(
                'session_key' => $session_key,
                'expires'     => date('Y-m-d H:i:s', strtotime($expire)),
                'last_seen'   => 'now()',
                'browser'     => $user_browser));

        $query_data['clause'] = array(
            array('operator' => 'AND',
                'conditions' => array(
                    'ip_address' => $ip,
                    'user_id'    => $user_id)));

        // Query the database updating the user session information
        if ($this->Theamus->DB->update_table_row(
                $this->Theamus->DB->system_table("user-sessions"),
                $query_data['data'],
                $query_data['clause']) != false) {

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
        $browser = $this->Theamus->Call->get_browser();

        // Define SQL friendly variables
        $ip             = $_SERVER['REMOTE_ADDR'];
        $user_browser   = $browser['name']." ".$browser['version'];

        // Get the user information, if possible
        $this->get_user_sessions(false, $user_id);

        // Check if the user already has a session on this computer, it's just expired
        foreach ($this->user_sessions as $user_session) {
            if (!isset($user_session['ip_address'])) continue;
            if ($user_session['ip_address'] == $ip) {
                if (strtotime($user_session['expires']) > time()) {
                    // Define the session key, set the cookies and return
                    $this->set_cookies($user_id, $user_session['session_key'], $expire);

                    return true;
                } else {
                    // Update the user session information and return
                    return $this->update_user_session($user_id, $session_key, $expire, $ip, $user_session);
                }
            }
        }

        // Define the query to add to the database
        $query = $this->Theamus->DB->insert_table_row(
            $this->Theamus->DB->system_table("user-sessions"),
            array(
                'session_key' => $session_key,
                'ip_address'  => $ip,
                'expires'     => date('Y-m-d H:i:s', $expire),
                'last_seen'   => 'now()',
                'browser'     => $user_browser,
                'user_id'     => $user_id));

        if ($query) {
            // Set the cookies
            $this->set_cookies($user_id, $session_key, $expire);

            return true;
        }
        return false;
    }

    /**
    * Sends a user to the login form with the current address attached for routing
    */
   public function send_to_login() {
       $protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
       $url = urlencode($protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
       $login_url = $this->Theamus->base_url."accounts/login?redirect=$url";
       header("Location: $login_url");
   }

}