<?php

/**
 * tUser - Theamus user information class
 * PHP Version 5.5.3
 * Version 1.0
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Matthew Tempinski (Eyraahh) <matt.tempinski@theamus.com>
 * @copyright 2014 Matthew Tempinski
 */

class tUser {
    /**
     * Holds the Theamus system data class
     *
     * @var object $tDataClass
     */
    private $tDataClass;


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
        $this->tDataClass->disconnect();
        return true;
    }


    /**
     * Defines variables that will be used within the class
     *
     * @return boolean
     */
    private function initialize_variables() {
        $this->cookies = filter_input_array(INPUT_COOKIE);
        $this->tDataClass = new tData();
        $this->tData = $this->tDataClass->connect();
        $this->tDataClass->prefix = $this->tDataClass->get_system_prefix();
        return true;
    }


    /**
     * In an attempt to stop people from requesting things they shouldn't be,
     *  this function will set a hash to a user and only allow this user to make calls.
     *
     * If the cookie doesn't exist or match, they can't do anything.
     */
    private function set_420hash() {
        // Define the hash variables
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $date = date("Y-d-m");
        $server_ip = $_SERVER['SERVER_ADDR'];

        // Define the hash
        $hash = md5($user_ip.$date.$server_ip);

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
            $q = $this->tData->query("SELECT * FROM `".$this->tDataClass->prefix."_users` WHERE ".
                "`id`='".$this->cookies['userid']."' ".
                "&& `session`='".$this->cookies['session']."'");

            $this->user = $this->tDataClass->check_query_and_return($q);
            if ($this->user == false) $this->force_logout();
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
        $q = $this->tData->query("SELECT * FROM `".$this->tDataClass->prefix."_users` WHERE `id`='$id'");
        if ($q->num_rows > 0) return $q->fetch_assoc();
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
            $q = $this->tData->query("SELECT `permissions` FROM `".$this->tDataClass->prefix."_groups` ".
                "WHERE `alias`='".$group."'");
            $qd = $this->tDataClass->check_query_and_return($q);
            $permissions = explode(",", $qd['permissions']);
            if (in_array($permission, $permissions)) $ret[] = "true";
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
        if ($this->check_login()) {
            if ($this->user == false) {
                @session_start();
                session_destroy();
                setcookie("session", "", 30, "/");
                setcookie("userid", "", 30, "/");

                return true;
            }
        }
        return false;
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
}