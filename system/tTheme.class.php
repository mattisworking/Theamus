<?php

/**
 * tTheme - Theamus theme parsing class
 * PHP Version 5.5.3
 * Version 1.2
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Eyrah Temet (Eyraahh) <info@theamus.com>
 */

class tTheme {
    /**
     * Holds all of the information relative to the user
     *
     * @var object $tUser
     */
    private $tUser;


    /**
     * Mysqli object
     *
     * @var object $tData
     */
    private $tData;


    /**
     * Holds the data given by the call
     *
     * @var array $data
     */
    protected $data;


    /**
     * Holds the data found in the theme's configuration file
     *
     * @var array $config
     */
    protected $config;


    /**
     * Holds the data that is accessible to the theme
     *
     * @var array $nice_data
     */
    private $nice_data;


    /**
     * The template file found in the theme's configuration settings
     *
     * @var string $templage
     */
    private $template;


    /**
     * True/False on whether or not the admin panel is accessible
     *
     * @var boolean $admin
     */
    public $admin;


    /**
     * Starts and initializes variables to be used by the class.
     * Then performs a try/catch statement to check/run the theme parsing
     *
     * @param array $data
     */
    public function __construct($data) {
        $this->data = $data;
        $this->tUser = new tUser();
        $this->tData = new tData();

        // Define a class variable that determines a connection to the database or not
        $this->no_database = isset($data['no_database']) && $data['no_database'] == true ? true : false;

        // Don't connect to the database if it isn't required to do so
        if ($this->no_database == false) {
            $this->tData->db = $this->tData->connect(true);
            $this->tData->prefix = $this->tData->get_system_prefix();
        }

        try {
            $this->nice_data = $this->clean_data();
            $this->config = $this->get_config();
            $this->template = $this->get_template();
            $this->include_template();
        } catch (Exception $e) {
            die("<strong>Theamus Theme Error:</strong> ".$e->getMessage());
        }
    }


    /**
     * Gets the theme folder from the data given
     *
     * @return string
     */
    private function get_theme_folder() {
        $split = strpos($this->data['theme'], "\\") !== false ? "\\" : "/";
        $path = explode($split, trim($this->data['theme'], $split));
        return array_pop($path);
    }


    /**
     * Gets the platform settings from the database
     *
     * @return array
     * @throws Exception
     */
    private function get_settings() {
        if ($this->no_database == false) {
            $query = $this->tData->select_from_table($this->tData->prefix."_settings");
            if (!$query) throw new Exception("Error getting system settings information from the database.");
            return $this->tData->fetch_rows($query);
        } else {
            return;
        }
    }


    /**
     * Cleans the incoming data from the call and sanitizes it to be called from
     *  the theme
     *
     * @return array
     */
    private function clean_data() {
        $settings = $this->get_settings();
        $ret['title'] = isset($this->data['title']) ? $this->data['title']." - ".$this->data['name'] : $this->data['name'];
        $ret['header'] = isset($this->data['header']) ? $this->data['header'] : "";
        $ret['theme_path'] = trim(web_path(trim(str_replace(ROOT, "", $this->data['theme']), "/")), "/")."/";
        $ret['wrapper_top'] = $this->tUser->is_admin() ? "style='top:37px;'" : "";
        $ret['site_name'] = urldecode(stripslashes(isset($settings['name']) ? $settings['name'] : ""));
        $ret['error_type'] = isset($this->data['error_type']) ? $this->data['error_type'] : 0;
        $ret['js'] = isset($this->data['js']) ? $this->data['js'] : "";
        $ret['css'] = isset($this->data['css']) ? $this->data['css'] : "";
        $ret['base'] = "<base href='".$this->data['base']."' />";
        $ret['page_alias'] = isset($this->data['page_alias']) ? $this->data['page_alias'] : "";
        $this->admin_panel = isset($this->data['admin']) ? $this->data['admin'] : "";
        $ret['has_admin'] = isset($this->data['admin']) && $this->tUser->is_admin() != false ? true : false;
        return $ret;
    }


    /**
     * Returns a variable value that is related to the page being loaded
     *
     * @param string $key
     * @return string
     */
    public function get_page_variable($key = "") {
        $variables = $this->clean_data();
        if (isset($variables[$key])) {
            return $variables[$key];
        } else {
            return "";
        }
    }


    /**
     * Includes an area file into the theme
     *
     * @param string $area
     * @return string
     */
    public function get_page_area($area = "") {
        // Include navigation
        if ($area == "extra-nav" && (!isset($this->data['nav']) || $this->data['nav'] == "")) {
            return;
        }

        // Include the admin panel
        if ($this->tUser->is_admin() && $area == "admin") {
            include $this->admin_panel;
        }

        // Include the template/area
        if (property_exists($this->config->areas, $area)) {
            $path = path($this->data['theme']."/".$this->config->areas->$area);
            if (file_exists($path)) {
                // Define classes to be used in the theme
                $tTheme = $this;
                $tUser = $this->tUser;
                $tData = $this->tData;

                // Include the file
                include $path;
                return;
            }
        }
        return;
    }


    /**
     * Gets a navigation list based on the location
     *
     * @param string $location
     * @return string
     */
    public function get_page_navigation($location = "", $classes = "") {
        if ($location == "main") {
            return show_page_navigation();
        } elseif ($location == "extra") {
            if ($this->data['nav'] != "") return extra_page_navigation($this->data['nav'], $classes);
            else return;
        } else {
            return show_page_navigation($location);
        }
    }


    /**
     * Includes the content into the theme
     *
     * @return
     */
    public function content() {
        $tData          = $this->tData;

        if ($this->no_database == false) {
            $tData->prefix  = $this->tData->prefix;
            $tData->db      = $this->tData->db;
        }

        $tFiles = new tFiles();
        $tPages = new tPages();
        $tUser  = $this->tUser;
        $tTheme = $this;

        if ($this->data['init-class'] != false) {
            ${$this->data['init-class']} = new $this->data['init-class'];
        }

        $url_params = $this->data['url_params'];

        $ajax_hash_cookie = isset($_COOKIE['420hash']) ? $_COOKIE['420hash'] : "";
        echo '<input type="hidden" id="ajax-hash-data" name="ajax-hash-data" value=\'{"key":"'.$ajax_hash_cookie.'"}\' />';
        include $this->data['file_path'];
        return;
    }


    /**
     * Includes the content into a blank page
     *
     * @return
     */
    public function blank_content() {
        $tDataClass = $this->tData;
        $tDataClass->prefix = $this->tData->prefix;
        $tData = $this->tData->db;

        $tFiles = new tFiles();
        $tUser = $this->tUser;
        $tPages = new tPages();
        $tTheme = $this;

        $url_params = $this->data['url_params'];

        if ($this->data['init-class'] != false) {
            ${$this->data['init-class']} = new $this->data['init-class'];
        }

        include $this->data['file_path'];
        return;
    }


    /**
     * Gets a value from the system database table to be used within a theme
     *
     * @param string $key
     * @return string
     */
    public function get_system_variable($key = "") {
        // Return nothing if we are given nothing
        if ($key == "") return "";

        // Sanitize the key and query the database
        $query = $this->tData->select_from_table($this->tData->prefix."_settings", array($key));

        // Check the query and gather the results
        if (!$query) {
            return "";
        } else {
            // Get the results
            $row = $this->tData->fetch_rows($query);

            // Check for the key's existance and return relatively
            if (isset($row[$key])) {
                return $row[$key];
            } else {
                return "";
            }
        }
    }


    /**
     * Gets information from the theme's configuration file
     *
     * @return array
     * @throws Exception
     */
    private function get_config() {
        $config_path = path($this->data['theme']."config.json");
        if (file_exists($config_path)) {
            return json_decode(file_get_contents($config_path));
        }
        throw new Exception("Cannot locate the theme configuration file.");
    }


    /**
     * Gets the template file requested based on the configuration file and the call settings
     *
     * @return string $ret
     * @throws Exception
     */
    private function get_template() {
		if (!isset($this->config->layouts)) {
			throw new Exception("The layouts are missing from the configuration file.");
		}
        for ($i = 0; $i < count($this->config->layouts); $i++) {
            if ($this->config->layouts[$i]->layout == "default") $ret = $this->config->layouts[$i]->file;
            elseif ($this->config->layouts[$i]->layout == $this->data['template']) $ret = $this->config->layouts[$i]->file;
        }
        if (isset($ret)) return $ret;
        throw new Exception("Cannot find the template file in the theme configuration.");
    }


    /**
     * Includes the template file into the page
     *
     * @throws Exception
     * @return
     */
    private function include_template() {
        $template_path = path($this->data['theme'].$this->template);
        if (file_exists($template_path)) {
            $tTheme = $this;
            include $template_path;
            return;
        } else {
            throw new Exception("Cannot find the template file in the directory structure.");
        }
    }

    /**
     * Gets variables from the 'themes-data' database table
     *
     * @param string $selector
     * @param string|boolean $key
     * @return array|string
     */
    public function get_theme_variable($selector = "", $key = false) {
        $return = array(); // Default = empty

        // Return nothing it the selector or key are empty
        if ($selector == "" || ($key == "" && $key != false)) {
            return "";
        }

        // Define the sql query
        $query_data = array("table_name" => $this->tData->prefix."_themes-data");
        if ($key == false) {
            $query_data['clause'] = array(
                "operator" => "AND",
                "conditions"=> array("selector" => $selector, "theme" => $this->get_theme_folder())
            );
        } else {
            $query_data['clause'] = array(
                "operator" => "AND",
                "conditions"=> array("selector" => $selector, "theme" => $this->get_theme_folder(), "key" => $key)
            );
        }

        // Query the database
        $query = $this->tData->select_from_table($query_data['table_name'], array(), $query_data['clause']);

        // Check the query and return relevant results
        if (!$query) {
            return "";
        } else {
            $results = $this->tData->fetch_rows($query);

            if (isset($results[0])) {
                // Loop throught all of the results
                $i = 0;
                foreach($results as $item) {
                    $return[$i]['id']           = $item['id'];
                    $return[$i][$item['key']]   = stripslashes(urldecode($item['value']));
                    $return[$i]['selector']     = $item['selector'];
                    $i++;
                }
            } else {
                $return['id']               = $results['id'];
                $return[$results['key']]    = stripslashes(urldecode($results['value']));
                $return['selector']         = $results['selector'];
            }

            return $return;
        }
    }
}