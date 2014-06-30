<?php

/**
 * tCall - Theamus content control class
 * PHP Version 5.5.3
 * Version 1.2
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Eyrah Temet (Eyraahh) <info@theamus.com>
 */

class tCall {
    /**
     * The base URL
     *
     * e.g. "http://www.yoursite.com/"
     *
     * @var string $base_url
     */
    private $base_url;


    /**
     * The parameters made from the URL
     *
     * @var array $parameters
     */
    private $parameters;


    /**
     * Whether or not the call is a page from the database or not
     *
     * @var boolean $page
     */
    private $page = false;


    /**
     * When a call is for a page from the database, this is the alias to grab it
     *
     * @var string $page_alias
     */
    private $page_alias;


    /**
     * Holds the configuration settings from the feature's configuration files
     *
     * @var array $feature
     */
    private $feature;


    /**
     * The folder in which the feature exists
     *
     * @var string $feature_folder
     */
    private $feature_folder;


    /**
     * The folder to look in depending on the call
     *
     * @var string $look_in_folder
     */
    private $look_in_folder;


    /**
     * Path to the folders in the feature to where the file lives
     *
     * @var string $feature_path_folders
     */
    private $feature_path_folders;


    /**
     * File that is being called
     *
     * @var string $feature_file
     */
    private $feature_file;


    /**
     * Complete path to the file that's being called
     *
     * @var string $complete_file_path
     */
    private $complete_file_path;


    /**
     * Class to initiate when the page loads
     *
     * @var string $init_class
     */
    private $init_class;


    /**
     * Object tUser
     *
     * See: ROOT/system/tUser.class.php
     *
     * @var object $tUser
     */
    private $tUser;


    /**
     * Object tData
     *
     * See ROOT/system/tData.class.php
     *
     * @var object $tData
     */
    private $tData;


    /**
     * Object tPages
     *
     * See ROOT/system/tPages.class.php
     *
     * @var object $tPages
     */
    private $tPages;


    /**
     * Object tFiles
     *
     * See ROOT/system/tFiles.class.php
     *
     * @var object tFiles
     */
    private $tFiles;


    /**
     * Holds the value of whether or not the call is of an AJAX/outside origin
     *
     * @var boolean $ajax
     */
    private $ajax = false;


    /**
     * Holds the bool that says if it's an API call or not
     *
     * @var bool api
     */
    private $api = false;


    /**
     * Defines where the api call is coming from
     *
     * @var boolean $api_from
     */
    private $api_from = false;


    /**
     * Defines the status of the api's success
     *
     * @var boolean
     */
    private $api_fail = false;


    /**
     * Lets the deconstruct function know whether or not to disconnect from the database or not
     *
     * @var boolean $no_init
     */
    private $no_init = false;


    /**
     * Initiates the class and defines the class variables
     *
     * @param string $params
     * @return boolean
     */
    public function __construct($params = false) {
        if (gettype($params) == "string") {
            $this->initiate($params);
            return true;
        } else {
            $this->no_init = true;
        }
    }


    /**
     * Destructs the class, closing the class' database connection
     *
     * @return boolean
     */
    public function __destruct() {
        if ($this->no_init == false) {
            $this->tData->disconnect();
            return true;
        }
    }


    /**
     * Handles the call and shows the appropriate content based on what is needed
     *
     * @return boolean
     */
    public function handle_call() {
        $call = $this->define_call();
        $path = $this->define_complete_path($call);

        $this->show_page_info(array("call" => $call));

        if (($path == true && $call['do_call'] != false) || $call['type'] == "system") {
            $this->$call['do_call']();
        }
        return true;
    }


    /**
     * Initiates class variables and some site configuration settings
     *
     * @return boolean
     */
    private function initiate($params) {
        $this->define_private_variables();
        $this->clean_global_variables();
        $this->display_errors();

        $this->base_url = $this->define_url();
        define("base_url", $this->define_url());
        $this->parameters = $this->define_parameters($params);

        return true;
    }


    /**
     * Defines the complete file path to wherever the user is trying to get to.
     *
     * @param array $call
     * @return boolean
     */
    private function define_complete_path($call) {
        $post = filter_input_array(INPUT_POST);
        if ($post['ajax'] != 'system') {
            $this->feature_folder = $this->define_feature();

            $this->feature['config'] = $this->feature_configuration();

            $this->look_in_folder = $this->look_in_folder($call['look_folder']);
            $this->feature_path_folders = $this->define_feature_folders();
            $file_info = $this->define_feature_file();
            $this->feature_file = $file_info['feature_file'];
            $this->complete_file_path = $file_info['complete_path'];

            $this->feature['files'] = $this->feature_files_configuration();

            $this->handle_issues();
            return true;
        }
        return false;
    }


    /**
     * Defines variables that this class will use
     *
     * @return boolean
     */
    private function define_private_variables() {
        $this->tUser = new tUser();

        $this->tData = new tData();
        $this->tData->db = $this->tData->connect(true);
        $this->tData->prefix = DB_PREFIX;

        $this->tPages = new tPages();

        $this->tFiles = new tFiles();

        return true;
    }


    /**
     * Displays errors for PHP based on the site configuration
     *
     * @return boolean
     */
    private function display_errors() {
        if ($this->tData) {
            $settings = defined('TM_SETTINGS') ? unserialize(TM_SETTINGS) : array();
            if (isset($settings['display_errors'])) {
                ini_set("display_errors", $settings['display_errors']);
            } else {
                ini_set("display_errors", 0);
            }
        }
        return true;
    }


    /**
     * Determines whether or not developer mode is turned on
     *
     * @return boolean
     */
    private function developer_mode() {
        if ($this->tData) {
            $settings = defined('TM_SETTINGS') ? unserialize(TM_SETTINGS) : array();
            return isset($settings['developer_mode']) && $settings['developer_mode'] == "1" ? true : false;
        }
    }


    /**
     * Shows information related to a page if developer mode is on
     *
     * To use, add "/.info" to any URL
     * To get a specific subject add "/[subject]" after the info delimiter
     *      e.g. "/.info/get/" or ".info/get/post/"
     *
     * @return
     */
    private function show_page_info($args) {
        if ($this->developer_mode()) {
            if (in_array(".info", $this->parameters)) {
                $info_position = array_search(".info", $this->parameters) + 1;
                $ret['included_files'] = get_included_files();
                $ret['get']     = $_GET;
                $ret['post']    = $_POST;
                $ret['server']  = $_SERVER;
                $ret['call']    = $args['call'];
                die(Pre($this->get_requested_info($ret, $info_position)));
            }
        }
        return;
    }


    /**
     * Defines the requested page information to show out of all the page information
     *
     * This function goes hand-in-hand with show_page_info()
     *
     * @param array $info
     * @param int $start
     * @return array $ret
     */
    private function get_requested_info($info, $start) {
        $params = $this->parameters;
        if ((count($params) - 1) >= $start) {
            for ($i = $start; $i < count($params); $i++) {
                $ret[$params[$i]] = $info[$params[$i]];
            }
        } else $ret = $info;
        return $ret;
    }


    /**
     * Checks the AJAX request hash
     */
    private function check_ajax_hash($feature = array()) {
           // Define the 420hash
           $post_hash = filter_input(INPUT_POST, "ajax-hash-data");
           $get_hash = filter_input(INPUT_GET, "ajax-hash-data");

           // Check if a feature key is something we should look at
           $feature_key = false;
           if (isset($feature['api']['key'])) {
               $feature_key = true;
           }

           // Define the hash
           $request_hash = $post_hash == "" ? $get_hash : $post_hash;
           $hash = json_decode(urldecode($request_hash), true);

           // Define the hash cookie
           $hash_cookie = isset($_COOKIE['420hash']) ? $_COOKIE['420hash'] : false;

           // Die if the 420hash cookie doesn't exist
           if ($hash_cookie == false && $this->api == false) {
               die("No 420hash defined.");
           }

           // Throw an error if the hash is blank
           if ($hash == "") {
               $this->api == false ? die("No hash defined.") : $this->api_fail = "No hash defined.";
           }

           // Check the hash cookie
           if ($hash_cookie != $hash['key'] && $this->api == false) {
               die("Hashfail please refresh.");
           }

           // Perform checks based on a feature key or not
           if ($feature_key == true) {
               if (($hash['key'] != $feature['api']['key'] && $this->api == true) || $this->api_from != "php") {
                   $this->api_fail = "Invalid API key.";
               }
           } else {
               if ($hash['key'] != $this->tData->get_hash() && $this->api == true && $this->api_from == false) {
                   $this->api_fail = "Invalid API key.";
               }
           }
       }

    /**
     * Defines where to look and what to call for every page call
     *
     * @return array $ret
     */
    private function define_call() {
        $this->install = false;

        $post = filter_input_array(INPUT_POST);
        $get = filter_input_array(INPUT_GET);
        if (!isset($post['ajax']) && !isset($get['ajax'])) {
            require path(ROOT."/system/tInstall.class.php");
            $install = new tInstall($this->base_url);
            $installed = $install->run_installer();

            $this->install = $installed ? true : false;

            $ret['type'] = "regular";
            $ret['look_folder'] = "view";
            $ret['do_call'] = "show_page";
        } else {
            $this->ajax = true;
            $ajax = $api_from = false;
            if (isset($post['ajax'])) $ajax = $post['ajax'];
            if (isset($get['ajax']) && $ajax == false) $ajax = $get['ajax'];
            if ($ajax == false) die("AJAX type cannot be found.");

            switch ($ajax) {
                case "script":
                    $ret['type'] = "script";
                    $ret['look_folder'] = "script";
                    $ret['do_call'] = "do_ajax";
                    break;
                case "include":
                    $ret['type'] = "include";
                    $ret['look_folder'] = "view";
                    $ret['do_call'] = "include_page";
                    break;
                case "system":
                    $ret['type'] = "system";
                    $ret['look_folder'] = "";
                    $ret['do_call'] = "include_system_page";
                    break;
                case "api":
                    $ret['type'] = "api";
                    $ret['look_folder'] = "";
                    $ret['do_call'] = "run_api";
                    $this->api = true;

                    if (isset($post['api-from'])) $api_from = $post['api-from'];
                    if (isset($get['api-from']) && $api_from == false) $api_from = $get['api-from'];

                    break;
                default: false;
            }

            $this->api_from = $api_from;
        }
        return $ret;
    }


    /**
     * Defines parameters based on the url
     *
     * @param string $params
     * @return array $ret
     */
    private function define_parameters($params) {
        $ret = array();
        if ($params != "") {
            $temp = trim($params, "/");
            $ret = explode("/", $temp);
        }
        return $ret;
    }


    /**
     * Determines whether or not the user is trying to go to a page that exists
     * in the database
     *
     * @return boolean
     */
    private function determine_page() {
        if ($this->tData->db) {
            $query = $this->tData->select_from_table($this->tData->prefix."pages", array(), array("operator" => "", "conditions" => array("alias" => $this->parameters[0])));

            if ($query != false) {
                if ($this->tData->count_rows($query) == 0) {
                    return false;
                }

                $page = $this->tData->fetch_rows($query);

                $theme = explode(":", $page['theme']);
                $file = $theme[count($theme)-1];

                if ($page['navigation'] == "") $navigation = "";
                else {
                    $navigation = array();
                    $nav = explode(",", $page['navigation']);
                    foreach($nav as $n) {
                        $link = explode("::", $n);
                        $navigation[$link[0]] = isset($link[1]) ? $link[1] : "";
                    }
                }

                $this->page_alias = $this->parameters[0];

                $this->feature['files']['theme']    = $file;
                $this->feature['files']['nav']      = $navigation;
                $this->feature['files']['header']   = $page['title'];
                $this->feature['files']['title']    = $page['title'];

                return true;
            }
        }
        return false;
    }


    /**
     * Defines the feature folder that was asked for in the URL then shifts
     * the URL to omit said folder
     *
     * @return string $feature
     */
    private function define_feature() {
        $params = $this->parameters;
        if ($this->install == true) {
            $feature = "install";
        } elseif (array_key_exists(0, $params)) {
            $feature_folder = strtolower($params[0]);
            $feature_path = path(ROOT."/features/$feature_folder");

            if (is_dir($feature_path)) {
                $feature = $feature_folder;
            } elseif ($this->determine_page()) {
                $this->page_alias = $params[0];
                $feature = "pages";
                $this->page = true;
            } else {
                $feature = false;
                $this->error = true;
            }
        } else {
            $feature = "default";
        }

        array_shift($this->parameters);
        return $feature;
    }


    /**
     * Defines the folder in which the system needs to look to find the right
     * file to display
     *
     * @param string $look_folder
     * @return string $ret
     */
    private function look_in_folder($look_folder) {
        switch ($look_folder) {
            case "view": $ret = "views"; break;
            case "script" :
                $folder = $this->get_custom_folder("scripts");
                $ret = $folder ? $folder : "php";
                break;
            default :
                $ret = "views";
                break;
        }
        return "/$ret/";
    }


    /**
     * Defines the folders to the path given by the url.  It will keep looking
     * for folders as long as it can and shift the parameters to omit those folders
     *
     * @return string $folders
     */
    private function define_feature_folders() {
        $folders = false;
        if ($this->feature_folder != false) {
            $folder_path = path(ROOT."/features/$this->feature_folder$this->look_in_folder");
            $folders = "";

            if (count($this->parameters) > 0) {
                foreach ($this->parameters as $param) {
                    if (is_dir($folder_path . "/" . $param)) {
                        $folder_path .= $param . "/";
                        $folders .= $param . "/";
                        array_shift($this->parameters);
                    }
                }
            }
        }

        return $folders;
    }


    /**
     * Defines the file that was called and the entire file path to include later on
     *
     * @return array|boolean $ret
     */
    private function define_feature_file() {
        $file = $file_path = false;
        if ($this->feature_folder != false) {
            $extension = ".php";

            if (array_key_exists(0, $this->parameters)) {
                $file = $this->parameters[0].$extension;
                array_shift($this->parameters);
            } elseif ($this->page == true) {
                $file = "show-page.php";
            } else {
                $file = "index".$extension;
            }

            $path = path(ROOT."/features/$this->feature_folder$this->look_in_folder");

            if ($this->feature_path_folders != false) $path .= $this->feature_path_folders;

            $filepath = file_exists($path.$file) ? $path.$file : false;

            $ret = array("feature_file" => $file, "complete_path" => $filepath);
            return $ret;
        }
        return false;
    }


    /**
     * Handles all issues that accumulated during the defining of the feature's
     * path.
     *
     * @return boolean
     */
    private function handle_issues() {
        $message = false;

        if (empty($this->feature['files'])
            && $this->feature['config'] == false) {
            $message = 100;
        }

        if ($this->complete_file_path == false
            || $this->feature_folder == false
            || $this->feature_file == false) {
            $message = 404;
        }

        if ($message != false) {
            if ($this->api == true) die($this->run_api());
            else die($this->error_page($message));
        }
        return true;
    }


    /**
     * Define's a feature's information from the database
     *
     * @return boolean|array
     */
    private function get_feature_information() {
        if ($this->install == false) {
            $query_data = array(
                "table"     => $this->tData->prefix."features",
                "clause"    => array(
                    "operator"  => "",
                    "conditions"=> array("alias" => $this->feature_folder)
                )
            );
            $query = $this->tData->select_from_table($query_data['table'], array(), $query_data['clause']);

            if ($query) {
                $results = $this->tData->fetch_rows($query);

                if (count($results) > 0) {
                    return $results;
                }
            }
        } else {
            return array("groups" => "everyone", "enabled" => 1);
        }
        return false;
    }


    /**
     * Shows content with the site's theme surrounding it
     *
     * @return
     */
    private function show_page() {
        $feature_info = $this->get_feature_information();

        foreach (explode(',', $feature_info['groups']) as $group) {
            $in_group[] = $this->tUser->in_group($group) ? true : false;
        }

        if (!in_array(true, $in_group) && $this->tUser->user == false) {
            send_to_login();
        } elseif (!in_array(true, $in_group) && $this->tUser != false) {
            die($this->error_page());
        }
        if ($feature_info['enabled'] == 0) die($this->error_page());

        $this->tUser->set_420hash();

        if ($this->install == false) {
            $settings = defined('TM_SETTINGS') ? unserialize(TM_SETTINGS) : array();
        } else {
            $settings['name'] = "Theamus Installation";
        }

        $data = $this->define_theme_data($settings['name']);
        if ($this->define_classes()) {
            $data['init-class'] = $this->init_class;
        } else {
            $data['init-class'] = false;
        }

        unset($settings, $q);
        new tTheme($data);
        return;
    }


    /**
     * Defines data that will be sent to the theme when the page loads
     *
     * @param string $name
     * @return array $data
     */
    private function define_theme_data($name) {
        $data['name']       = $name;
        $data['base']       = $this->base_url;
        $data['css']        = $this->get_css();
        $data['js']         = $this->get_javascript();
        $data['title']      = @$this->feature['files']['title'];
        $data['header']     = @$this->feature['files']['header'];
        $data['feature']    = $this->feature_folder;
        $data['nav']        = isset($this->feature['files']['nav']) ? $this->feature['files']['nav'] : "";
        $data['admin']      = $this->tUser->user && $this->tUser->is_admin() ? $this->include_admin() : "";
        $data['theme']      = $this->define_theme_path();
        $data['template']   = isset($this->feature['files']['theme']) ? $this->feature['files']['theme'] : "default";
        $data['file_path']  = $this->complete_file_path;
        $data['page_alias'] = $this->page_alias;
        $data['url_params'] = $this->parameters;
        $data['no_database']= $this->install;

        return $data;
    }


    /**
     * Defines the path to the theme that the site is currently using
     *
     * This function goes hand-in-hand with show_page()
     *
     * @param string $type
     * @return string
     */
    private function define_theme_path($type = "html") {
        if ($this->install == false) {
            $tData          = $this->tData;
            $tData->prefix  = $this->tData->prefix;
            $tData->db      = $this->tData->db;

            $folder = "default";
            if ($tData) {
                $query_data = array(
                    "table" => $this->tData->prefix."themes",
                    "clause"=> array(
                        "operator"  => "",
                        "conditions"=> array("active" => 1)
                    )
                );
                $query = $this->tData->select_from_table($query_data['table'], array(), $query_data['clause']);

                if ($this->tData->count_rows($query) > 0) {
                    $results = $this->tData->fetch_rows($query);
                    $folder = $results['alias'];
                }
            }

            $path = path(ROOT."/themes/$folder/");
        } else {
            $path = path(ROOT."/themes/installer/");
        }

        return $path;
    }


    /**
     * Defines the feature configuration settings into an array
     *
     * @return array
     */
    private function feature_configuration() {
        $feature_path = path(ROOT."/features/$this->feature_folder/");

        $ret = array();
        if (file_exists($feature_path."config.php")) {
            include $feature_path."config.php";
            if (isset($feature) && is_array($feature)) {
                $ret = $feature;
                if (isset($feature['functions'])) $this->load_config_functions($feature['functions']);
            }
        }

        return $ret;
    }


    /**
     * Takes the given information from the config.php file for the loading feature
     *  and loads any miscellaneous function files before files.info.php loads
     *
     * @param array $config
     * @return
     */
    private function load_config_functions($config) {
        $feature_path = path(ROOT."/features/$this->feature_folder/");
        if (isset($config['file'])) {
            $arr = !is_array($config['file']) ? array($config['file']) : $config['file'];
            foreach ($arr as $a) {
                if (file_exists($feature_path."/".$a)) include $feature_path."/".$a;
            }
        }
        return;
    }


    /**
     * Defines the feature file configuration settings into an array
     *
     * @return array $ret
     */
    private function feature_files_configuration() {
        $tUser = $this->tUser;
        $feature_path = path(ROOT."/features/$this->feature_folder/");
        $folders = explode("/", $this->feature_path_folders);
        $file = $this->feature_path_folders.$this->feature_file;
        $location = urldecode(filter_input(INPUT_POST, "location"));
        $post_ajax = filter_input(INPUT_POST, "ajax");
        $get_ajax = filter_input(INPUT_GET, "ajax");
        $ajax = $post_ajax == "" ? $get_ajax : $post_ajax;

        $file_info = array();
        if (file_exists($feature_path."files.info.php")) {
            include $feature_path."files.info.php";
            if (isset($feature) && is_array($feature)) $file_info = $feature;
        }

        $ret = !empty($this->feature['files']) ? array_merge($file_info, $this->feature['files']) : $file_info;

        // Check the hash
        if ($this->ajax == true) {
            $this->check_ajax_hash($ret);
        }
        return $ret;
    }


    /**
     * Defines the URL that was used to make the current call
     *
     * @return string
     */
    private function define_url() {
        $protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
        $domain = $_SERVER['HTTP_HOST'];
        $directory = dirname($_SERVER['PHP_SELF']) . "/";

        return trim($protocol.$domain.$directory, "/")."/";
    }


    /**
     * Defines the custom javascript or css folder that is used by the feature in order to
     * call custom files
     *
     * This function goes hand-in-hand with get_javascript() and get_css()
     *
     * @return string|boolean
     */
    private function get_custom_folder($type) {
        $config = $this->feature['config'];
        if (array_key_exists($type, $config)) {
            if (array_key_exists("folder", $config[$type])) {
                $path = path(ROOT."/features/".$this->feature_folder."/".$config[$type]['folder']);
                if (is_dir($path)) return $config[$type]['folder'];
            }
        }
        if (is_dir(path(ROOT."/".$this->feature_folder."/js"))) return $type;
        return false;
    }


    /**
     * Defines the javascript or css files given by the feature's configuration files
     * into an array to be written into a string later on
     *
     * This function goes hand-in-hand with get_javascript() and get_css()
     *
     * @return array
     */
    private function get_custom_files($type) {
        $files = $this->feature['files'];
        if (array_key_exists($type, $files)) {
            if (array_key_exists("file", $files[$type])) {
                if (is_array($files[$type]['file'])) return $files[$type]['file'];
                return array();
            }
        }
        return array();
    }


    /**
     * Define's all of the CSS that is required for the page to use
     *
     * @param boolean $for_ajax
     * @return string
     */
    private function get_css($for_ajax = false) {
        $ret = array();
        if ($for_ajax == false) {
            $ret[] = $this->default_css();
        }

        $folder = $this->get_custom_folder("css");
        $path = "features/$this->feature_folder/$folder";

        if ($folder != false) {
            $files = $this->get_custom_files("css");
            $ret[] = $this->define_css_tags($path, $files, $for_ajax);
        }

        return implode("\n", $ret);
    }


    /**
     * Defines the default css to be used on every page
     *
     * This function goes hand-in-hand with get_css()
     *
     * @return string
     */
    private function default_css() {
        $ret = array(
            "<link rel='stylesheet' href='system/styles/css/theme.css' />",
            "<link rel='stylesheet' href='system/styles/css/ionicons/ionicons.min.css' />",
            "<link rel='stylesheet' href='system/external/prettify/prettify.css' />",
            "<link rel='stylesheet' href='system/editor/css/editor.css' />"
        );
        return implode("", $ret);
    }

    /**
     * Defines the css tags that will be placed in the header when the page loads
     *
     * This function goes hand-in-hand with get_css()
     *
     * @param string $path
     * @param array $files
     * @param boolean $for_ajax
     * @return string
     */
    private function define_css_tags($path, $files, $for_ajax) {
        $ret = array();
        if (!empty($files)) {
            foreach ($files as $f) {
                if ($for_ajax == false) {
                    $ret[] = "<link rel='stylesheet' href='$path/$f' />";
                } else {
                    $ret[] = "<input type='hidden' name='addstyle' value='$path/$f?x=".time()."' />";
                }
            }
        }
        return implode("", $ret);
    }


    /**
     * Define's all of the javascript that is required for the page to use.
     *
     * @param boolean $for_ajax
     * @return string
     */
    private function get_javascript($for_ajax = false) {
        $ret[] = $for_ajax != false ? "" : $this->default_javascript();

        $folder = $this->get_custom_folder("js");
        $path = "features/".$this->feature_folder."/".$folder;

        if ($folder != false) {
            $files = $this->get_custom_files("js");
            $ret[] = $this->define_javascript_tags($path, $files, $for_ajax);

            $scripts = $this->get_javascript_scripts();
            $ret[] = $this->define_javascript_scripts($scripts, $for_ajax);
        }

        return implode("", $ret);
    }


    /**
     * Defines default javascript to be loaded during the page load
     *
     * This function goes hand-in-hand with get_javascript()
     *
     * @return string
     */
    private function default_javascript() {
        $ret = array(
            "<script src='system/js/jquery.js'></script>",
            "<script src='system/external/shim/classlist.js'></script>",
            "<script src='system/js/ajax/ajax.js'></script>",
            "<script src='system/editor/js/editor.js'></script>",
            "<script src='system/js/main.js'></script>",
            "<script src='system/js/theme.js'></script>",
            "<script src='system/external/prettify/prettify.js'></script>",
            "<script src='system/external/rangy/rangy.js'></script>",
            $this->tUser->user && $this->tUser->is_admin() ? "<script src='themes/admin/js/admin.min.js'></script>" : "",
            "<script>theamus.info = ".$this->define_javascript_info()."</script>",
        );
        return implode("\n", $ret);
    }


    /**
     * Defines information to be passed to javasript on page load
     *
     * @return json $info
     */
    private function define_javascript_info() {
        $info = array(
            "site_base"    => $this->base_url,
            "feature"      => $this->feature_folder,
            "feature_file" => $this->feature_file
        );
        return json_encode($info);
    }


    /**
     * Defines the script tags provided by the feature's configuration files
     * into a string that will be run on the next call
     *
     * This function goes hand-in-hand with get_javascript()
     *
     * @param string $path
     * @param array $files
     * @param boolean $for_ajax
     * @return string
     */
    private function define_javascript_tags($path, $files, $for_ajax) {
        $ret = array();
        if (!empty($files)) {
            foreach ($files as $f) {
                if ($for_ajax == false) {
                    $ret[] = "<script src='$path/$f'></script>";
                } else {
                    $ret[] = "<input type='hidden' name='addscript' ".
                            "value='$path/$f?x=".time()."' />";
                }
            }
        }
        return implode("", $ret);
    }


    /**
     * Defines the scripts given by the feature's configuration files into an
     * array to be written into a string later
     *
     * This function goes hand-in-hand with get_javascript()
     *
     * @return array
     */
    private function get_javascript_scripts() {
        $files = $this->feature['files'];
        if (array_key_exists("js", $files)) {
            if (array_key_exists("script", $files['js'])) {
                if (is_array($files['js']['script'])) return $files['js']['script'];
                return array();
            }
        }
        return array();
    }


    /**
     * Defines the scripts provided by the feature's configuration files into
     * into script tags and returns it as a string to be printed and run
     *
     * This function goes hand-in-hand with get_javascript()
     *
     * @param array $scripts
     * @param boolean $for_ajax
     * @return string
     */
    private function define_javascript_scripts($scripts, $for_ajax) {
        $ret = array();
        if (!empty($scripts)) {
            foreach ($scripts as $s) {
                if ($for_ajax == false) $ret[] = "<script>$s</script>";
            }
        }
        return implode("", $ret);
    }


    /**
     * Defines a class that is specified by a feature, allows complete access
     * to that class with the variable of the class name.
     *
     * e.g. $NewClass = new NewClass;
     *
     * @return boolean
     */
    private function define_classes() {
        $class_folder = $this->get_class_folder();
        $class_info = $this->get_class_info();

        if ($class_folder && $class_info) {
            $path = path(ROOT."/features/$this->feature_folder/$class_folder/");
            if ($this->include_class($path.$class_info['file'])) {
                return true;
            }
        }

        return false;
    }


    /**
     * Checks to see if a class file exists, then includes that file
     *
     * This function goes hand-in-hand with define_classes()
     *
     * @param string $path
     * @return boolean
     */
    private function include_class($path) {
        if (file_exists($path)) {
            include $path;
            return true;
        }
        return false;
    }


    /**
     * Gets the class folder from the provided feature configuration, if there
     * is a class folder to get, that is
     *
     * This function goes hand-in-hand with define_classes()
     *
     * @return boolean
     */
    private function get_class_folder() {
        $config = $this->feature['config'];

        if (is_array($config)) {
            if (array_key_exists("class", $config)) {
                if (array_key_exists("folder", $config['class'])) {
                    return $config['class']['folder'];
                }
            }
        }

        return false;
    }


    /**
     * Gets the class information (file, class name) from the provided feature
     * configuration
     *
     * This function goes hand-in-hand with define_classes()
     *
     * @return boolean
     */
    private function get_class_info() {
        $files = $this->feature['files'];

        if (is_array($files)) {
            if (array_key_exists("class", $files)) {
                if (array_key_exists("file", $files['class']) &&
                    array_key_exists("init", $files['class'])) {
                    $this->init_class = $files['class']['init'];
                    return array(
                        "file" => $files['class']['file'],
                        "init" => $files['class']['init']
                        );
                }
            }
        }

        return false;
    }


    /**
     * Shows an error page when required to
     *
     * @param string $type
     * @return boolean
     */
    private function error_page($type="404") {
        if ($this->install == false) {
            $settings = defined('TM_SETTINGS') ? unserialize(TM_SETTINGS) : array();
        } else {
            $settings['name'] = "Theamus Installation";
        }

        $data['name']       = $settings['name'];
        $data['base']       = $this->base_url;
        $data['title']      = $data['header'] = "Error";
        $data['theme']      = $this->define_theme_path();
        $data['template']   = "error";
        $data['error_type'] = $type;
        $data['nav']        = false;
        $data['css']        = $this->get_css();
        $data['js']         = $this->get_javascript();
        $data['no_database']= $this->install;

        new tTheme($data);
        return;
    }


    /**
     * Defines the path to the administration theme/functionality/panel
     *
     * @return string
     */
    private function include_admin() {
        return path(ROOT."/themes/admin/html.min.php");
    }


    /**
     * Runs a PHP script that was called via AJAX
     *
     * @return boolean
     */
    private function do_ajax() {
        $tFiles = $this->tFiles;
        $tUser = $this->tUser;
        $tPages = $this->tPages;

        $tData          = $this->tData;
        $tData->prefix  = $this->tData->prefix;
        $tData->db      = $this->tData->db;

        if ($this->define_classes()) {
            $init_class = $this->init_class;
            ${$init_class} = new $init_class;
        }

        $url_params = $this->parameters;

        include $this->complete_file_path;

        return true;
    }


    /**
     * Includes a page via  with no theme styling, just the styling of the
     * feature's configuration
     *
     * @return boolean
     */
    private function include_page() {
        $tFiles = $this->tFiles;
        $tUser = $this->tUser;
        $tPages = $this->tPages;

        $tData          = $this->tData;
        $tData->prefix  = $this->tData->prefix;
        $tData->db      = $this->tData->db;

        if ($this->define_classes()) {
            $init_class = $this->init_class;
            ${$init_class} = new $init_class;
        }

        $url_params = $this->parameters;

        echo $this->get_javascript(true);
        echo $this->get_css(true);

        include $this->complete_file_path;

        return true;
    }


    /**
     * Includes a file that was called via AJAX that lives in the system folder
     *
     * @return boolean
     */
    private function include_system_page() {
        $tFiles = $this->tFiles;
        $tUser = $this->tUser;
        $tPages = $this->tPages;

        $tData          = $this->tData;
        $tData->prefix  = $this->tData->prefix;
        $tData->db      = $this->tData->db;

        $pre_slash = "";
        if (substr(ROOT, 0, 1) == "/") {
            $pre_slash = "/";
        }

        $desired = filter_input(INPUT_GET, "params");
        $path = path($pre_slash.trim(ROOT."/system/$desired", "/").".php");

        if (file_exists($path)) include $path;
        return true;
    }


    /**
     * Includes a file as defined by the developer
     *
     * @param string $file
     * @param boolean $feature
     * @param boolean $absolute
     * @return boolean
     */
    public function include_file($file, $feature = false, $absolute = false) {
        $tFiles = $this->tFiles;
        $tUser = $this->tUser;
        $tPages = $this->tPages;

        $tData          = $this->tData;
        $tData->prefix  = $this->tData->prefix;
        $tData->db      = $this->tData->db;

        $path = $file.".php";
        if ($absolute == false) {
            $path = ROOT . "/features/";
            $path .= $feature == false ? $this->feature_folder : $feature;
            $path .= "/views/".$file.".php";
        }

        if (file_exists(path($path))) include path($path);
        return false;
    }


    /**
     * Takes the variables given from the POST and GET requests and strips
     *  anything that isn't relevant to the user
     *
     * @param array $input
     * @return array
     */
    private function define_api_variables($input) {
        $ret = array();
        $ignore = array("method_class", "method", "params", "ajax", "ajax-hash-data", "type", "url", "api-key", "api-from");
        foreach ($input as $key => $value) {
            if (!in_array($key, $ignore)) {
                if ($this->tData->string_is_json(urldecode($value))) {
                    $ret[$key] = json_decode(urldecode($value), true);
                } else {
                    $ret[$key] = urldecode($value);
                }
            }
        }
        return $ret;
    }


    /**
     * Runs an API request from a front end somewhere
     */
    private function run_api() {
        // Define both of the inputs
        $post = filter_input_array(INPUT_POST);
        $get = filter_input_array(INPUT_GET);

        // Determine which inputs to use
        $inp = false;
        if (is_array($post)) $inp = $post;
        if (is_array($get) && $inp == false) $inp = $get;

        // Define the return array, which will be shown as JSON and the error
        $return = array();
        $error = false;
        $response = "";
        $function_variables = $this->define_api_variables($inp);
        if (array_key_exists("data", $function_variables)) $function_variables = $function_variables['data'];

        // Determine the method and class (if applicable)
        if ($this->api_fail == false) {
            // Determine the method and class (if applicable)
            if (isset($inp['method_class']) && $inp['method_class'] != "") {
                if (isset($this->feature['config']['api']['class_file'])) {
                    // If the class file isn't already an array, make it one
                    $feature_class_files = $this->feature['config']['api']['class_file'];
                    if (!is_array($feature_class_files)) {
                        $class_files = array($feature_class_files);
                    } else {
                        $class_files = $feature_class_files;
                    }

                    // Loop through all of the class files, including them
                    foreach ($class_files as $cf) {
                        $class_file_path = path(ROOT."/features/".$this->feature_folder."/".$cf);
                        if (file_exists($class_file_path)) {
                            include_once $class_file_path;
                        } else {
                            $error = "The API class file based on the requested URL doesn't exist or could not be found.";
                        }
                    }

                    if (class_exists($inp['method_class']) && method_exists($inp['method_class'], $inp['method'])) {
                        $class = ${$inp['method_class']} = new $inp['method_class'];
                        $response = call_user_func(array($class, $inp['method']), $function_variables);
                    } else {
                        $error = "The class or method requested doesn't exist or couldn't be found.";
                    }
                } else {
                    $error = "There is no API class file defined for the requested URL.";
                }
            } elseif (isset($inp['method'])) {
                if (function_exists($inp['method'])) {
                    $response = call_user_func($inp['method'], $function_variables);
                } else {
                    $error = "The method being requested does not exist and therefore cannot run.";
                }
            } else {
                $error = "No method was defined.";
            }
        }

        // Define the response data and echo out the results
        $return['response']['data'] = "";
        $return['response']['status'] = 200;
        if ($error != false || $this->api_fail != false) {
            $return['error']['message'] = $this->api_fail == false ? $error : $this->api_fail;
        } else {
            $return['response']['data'] = $response;
            $return['error']['message'] = "";
        }
        $return['error']['status'] = $error != false || $this->api_fail != false ? 1 : 0;
        echo json_encode($return);
    }


    /**
     * Cleans all of the request variables that are sent through POST and GET
     *
     * @return
     */
    private function clean_global_variables() {
        $_GET = $this->clean_request_array("get");
        $_POST = $this->clean_request_array("post");

        return;
    }


    /**
     * Get's an array from the global variable array then decodes and trims it.
     * After it's done cleaning, it returns the new set of variables as an array
     *
     * This function goes hand-in-hand with clean_global_variables()
     *
     * @param string $type
     * @return array
     */
    private function clean_request_array($type) {
        $request = $GLOBALS["_".strtoupper($type)];
        $input = "INPUT_".strtoupper($type);
        if (constant($input)) {
            $filtered = filter_input_array(constant($input));
            foreach ($filtered as $key => $value) {
                unset($request[$key]);
                $request[$key] = trim(urldecode($value), "/");
            }
        }
        return $request;
    }

    public function get_browser() {
        $u_agent    = $_SERVER['HTTP_USER_AGENT'];
        $bname      = 'Unknown';
        $platform   = 'Unknown';
        $version    = "";

        // Get the platform
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Get the name of the agent
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif(preg_match('/Firefox/i',$u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif(preg_match('/Chrome/i',$u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif(preg_match('/Safari/i',$u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif(preg_match('/Opera/i',$u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif(preg_match('/Netscape/i',$u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // Get the version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>'.join('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {}

        $i = count($matches['browser']);
        if ($i != 1) {
            // Check if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            } else {
                $version= $matches['version'][1];
            }
        } else {
            $version= $matches['version'][0];
        }

        // Check if we have a number
        if ($version==null || $version=="") {
            $version="?";
        }

        // Return the information
        return array(
            'agent'     => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'   => $pattern
        );
    }
}