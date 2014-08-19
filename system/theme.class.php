<?php

/**
 * Theme - Theamus theme parsing class
 * PHP Version 5.5.3
 * Version 1.3.0
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Eyrah Temet (Eyraahh) <info@theamus.com>
 */
class Theme {
    /**
     * Holds the data given by the call
     *
     * @var array $data
     */
    public $data;


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
    public function __construct($t) {
        $this->Theamus = $t; // Make other Theamus classes usable

        // Define a class variable that determines a connection to the database or not
        $this->no_database = !$this->Theamus->DB->connection ? true : false;
    }


    /**
     * Loads the theme with the content into the browser
     *
     * THE FINAL STEP OF THE PAGE LOAD
     *
     * @param array $data
     */
    public function load_theme($data = array()) {
        $this->data = $data;

        try {
            $this->nice_data = $this->clean_data();
            $this->config = $this->get_config();
            $this->template = $this->get_template();
            $this->include_template();
        } catch (Exception $e) {
            echo "<strong>Theamus Theme Error:</strong> ".$e->getMessage();
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
     * Cleans the incoming data from the call and sanitizes it to be called from
     *  the theme
     *
     * @return array
     */
    private function clean_data() {
        $settings = !$this->Theamus->DB->connection ? $this->data : $this->Theamus->settings;


        $ret['title']       = isset($this->data['title']) ? $this->data['title']." - ".$this->data['name'] : $this->data['name'];
        $ret['header']      = isset($this->data['header']) ? $this->data['header'] : "";
        $ret['theme_path']  = trim($this->Theamus->web_path(trim(str_replace(ROOT, "", $this->data['theme']), "/")), "/")."/";
        $ret['site_name']   = urldecode(stripslashes(isset($settings['name']) ? $settings['name'] : ""));
        $ret['error_type']  = isset($this->data['error_type']) ? $this->data['error_type'] : 0;
        $ret['js']          = isset($this->data['js']) ? $this->data['js'] : "";
        $ret['css']         = isset($this->data['css']) ? $this->data['css'] : "";
        $ret['base']        = "<base href='".$this->data['base']."' />";
        $ret['page_alias']  = isset($this->data['page_alias']) ? $this->data['page_alias'] : "";
        $this->admin_panel  = isset($this->data['admin']) ? $this->data['admin'] : "";
        $ret['has_admin']   = isset($this->data['admin']) && $this->Theamus->User->is_admin() != false ? true : false;
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
        if ($this->Theamus->User->is_admin() && $area == "admin") {
            include $this->admin_panel;
        }

        // Include the template/area
        if (property_exists($this->config->areas, $area)) {
            $path = $this->Theamus->file_path($this->data['theme']."/".$this->config->areas->$area);
            if (file_exists($path)) {
                // Define classes to be used in the theme
                $Theamus = $this->Theamus;

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
            return $this->show_page_navigation();
        } elseif ($location == "extra") {
            if ($this->data['nav'] != "") return $this->extra_page_navigation($this->data['nav'], $classes);
            else return;
        } else {
            return $this->show_page_navigation($location);
        }
    }


    /**
     * Includes the content into the theme
     *
     * @return
     */
    public function content() {
        $Theamus = $this->Theamus;

        if ($this->data['init-class'] != false) {
            ${$this->data['init-class']} = new $this->data['init-class']($this->Theamus);
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
        $Theamus = $this->Theamus;

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

        $settings = $this->Theamus->settings;

        // Check for the key's existance and return relatively
        if (isset($settings[$key])) {
            return $settings[$key];
        } else {
            return "";
        }
    }


    /**
     * Gets information from the theme's configuration file
     *
     * @return array
     * @throws Exception
     */
    private function get_config() {
        $config_path = $this->Theamus->file_path($this->data['theme']."config.json");
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
        $template_path = $this->Theamus->file_path($this->data['theme'].$this->template);
        if (file_exists($template_path)) {
            $Theamus = $this->Theamus;
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
        $query_data = array("table_name" => $this->Theamus->DB->system_table("themes-data"));
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
        $query = $this->Theamus->DB->select_from_table($query_data['table_name'], array(), $query_data['clause']);

        // Check the query and return relevant results
        if (!$query) {
            return "";
        } else {
            $results = $this->Theamus->DB->fetch_rows($query);

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

    /**
     * Shows all of the relevant page navigation defined by the links in the database
     *
     * @return boolean
     */
    public function show_page_navigation($loc = "main", $child_of = 0) {
        $ret        = array();

        $query_data = array(
            "table"     => $this->Theamus->DB->system_table("links"),
            "columns"   => array("groups", "path", "text", "id"),
            "clause"    => array(
                "operator"  => "AND",
                "conditions"=> array("location" => $loc, "child_of" => $child_of)
            )
        );

        $query = $this->Theamus->DB->select_from_table($query_data['table'], $query_data['columns'], $query_data['clause']);

        if ($query != false && $this->Theamus->DB->count_rows($query) > 0) {
            $results = $this->Theamus->DB->fetch_rows($query);
            if (!isset($results[0])) {
                $results = array($results);
            }

            foreach ($results as $link) {
                $in = array();
                foreach (explode(",", $link['groups']) as $group) {
                    $in[] = $this->Theamus->User->in_group($group) ? "true" : "false";
                }

                if (in_array("true", $in)) {
                    $c = $this->Theamus->DB->select_from_table($query_data['table'], array(), array("operator" => "", "conditions" => array("child_of" => $link['id'])));
                    $ret[] = "<li>";
                    $ret[] = "<a href='".$link['path']."'>".$link['text']."</a>";
                    if ($this->Theamus->DB->count_rows($c) > 0) $ret[] = "<ul>";
                    $ret[] = $this->show_page_navigation($loc, $link['id']);
                    if ($this->Theamus->DB->count_rows($c) > 0) $ret[] = "</ul>";
                    $ret[] = "</li>";
                }
            }
        }

        return implode($ret);
    }


   /**
    * Shows navigation that is made for the html-nav layout.  As defined by
    *  static pages or features
    *
    * @param string $navigation
    * @return string $nav|boolean
    */
    public function extra_page_navigation($navigation, $classes = "") {
        if (!empty($navigation)) {
            $class = ($classes != "") ? "class='$classes'" : "";
            $nav = "<ul $class>";
            foreach ($navigation as $text => $path) {
                if ($text != "path") {
                    if ($path == "hr") $nav .= "<li class='nav-hr'><hr /></li>";
                    elseif (is_array($navigation[$text])) {
                        $nav .= "<li><a href='".$navigation[$text]['path']."'>".$text."</a>";
                        $nav .= extra_page_navigation($navigation[$text]);
                        $nav .= "</li>";
                    } else $nav .= "<li><a href='".$path."'>".$text."</a></li>";
                }
            }
            $nav .= "</ul>";

            return $nav;
        }
        return false;
    }
}