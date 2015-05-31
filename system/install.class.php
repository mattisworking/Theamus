<?php

/**
 * Install - Theamus installer class
 * PHP Version 5.5.3
 * Version 1.3.0
 * @package Theamus
 * @link http://www.theamus.com/
 * @author MMT (helllomatt) <mmt@itsfake.com>
 */
class Install {
    /**
     * Complete base url to the current website (e.g. http://www.mysite.com/)
     *
     * @var string $url
     */
    private $url;


    /**
     * Holds the answer to whether or not the database exists
     *
     * @var boolean $db_exists
     */
    private $db_exists;


    /**
     * Holds the answer to whether or not the database has been installed
     *
     * @var boolean $installed
     */
    private $installed;


    /**
     * Constructs the class, defining class-specific variables
     *
     * @param string $url
     * @return boolean
     */
    public function __construct($t, $url) {
        $this->Theamus = $t;

        $this->url = $url;
        $this->initiate_variables();
        return true;
    }


    /**
     * Defines any class related variables
     *
     * @return boolean
     */
    private function initiate_variables() {
        $this->config_file  = $this->check_configuration_file();
        $this->db_exists    = $this->check_database_existence();
        $this->installed    = $this->check_installation();
        return true;
    }


    /**
     * Checks for the existence of "config.php"
     *
     * @return boolean
     */
    private function check_configuration_file() {
        return file_exists($this->Theamus->file_path(ROOT."/config.php")) ? true : false;
    }


    /**
     * Performs a check to see whether or not the database credentials are valid
     *  and if the database exists
     *
     * It also defines the mysqli connection object if everything is ok
     *
     * @return boolean
     */
    private function check_database_existence() {
        return !$this->Theamus->DB->connection ? false : true;
    }


    /**
     * Checks to see if the site has been installed in the database or not
     *
     * @return boolean
     */
    private function check_installation() {
        $ret = isset($this->Theamus->settings['installed']) && $this->Theamus->settings['installed'] == 1 ? true : false;
        return isset($ret) ? $ret : false;
    }


    /**
     * If the site has not been installed, show the installer and go from there
     *
     * @return boolean
     */
    public function run_installer() {
        if (!$this->config_file || !$this->Theamus->DB->connection || !$this->installed) return false;
        return true;
    }
}