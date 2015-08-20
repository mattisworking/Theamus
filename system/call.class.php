<?php

/**
 * Call - Theamus content control class
 * PHP Version 5.5.3
 * Version 1.4.2
 * @package Theamus
 * @link http://www.theamus.com/
 * @author MMT
 */
class Call {
    /**
     * The base URL
     *
     * e.g. "http://www.yoursite.com/"
     *
     * @var string $base_url
     */
    public $base_url;


    /**
     * The parameters made from the URL
     *
     * @var array $parameters
     */
    public $parameters;


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
    protected $page_alias;


    /**
     * Holds the configuration settings from the feature's configuration files
     *
     * @var array $feature
     */
    public $feature;


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
     * @var string $init_classes
     */
    private $init_classes = array();

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
     * Boolean that will run the installer or not
     *
     * @var boolean $install
     */
    private $install = false;


    /**
     * String to define what the index file is for a folder
     *
     * @var string $folder_index
     */
    protected $folder_index;


    /**
     * Boolean to let Theamus know if an index file is implied or not
     *
     * @var boolean $folder_index_is_implied
     */
    protected $folder_index_is_implied;


    /**
     * When figuring out files, this lets Theamus know if it should shift
     * the parameters array anymore or not
     *
     * @var boolean $shift_parameters
     */
    protected $shift_parameters = true;
    
    
    /**
     * Return the content as JSON instead of the theme?
     * 
     * @var boolean $return_json
     */
    protected $return_json = false;


    /**
     * Connect this class to Theamus
     *
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t; // Make other Theamus classes usable

        return; // Return!
    }


    /**
     * Handles the call and shows the appropriate content based on what is needed
     *
     * @return
     */
    public function handle_call($params) {
        // Initiate the call with the parameters from the URL
        $this->initiate($params);
        
        // Checks to make sure a file isn't PHP
        $this->check_requested_file();
        
        // Define the type of call
        $call = $this->define_call();

        // Define the path to the file being requested
        $path = $this->define_complete_path($call);

        // Do the call that will handle the page
        if (($path == true && $call['do_call'] != false) || $call['type'] == "system") {
            $this->$call['do_call']();
        }

        return; // Return!
    }
    
    
    /**
     * Figures out the URI to the file and omits the overlapping file structure
     * to give the file path from the ROOT of the site's existence on the server
     * 
     * @return string
     */
    private function get_uri() {
        $root_array = array_values(array_filter(explode("/", ROOT)));
        $uri_array = array_values(array_filter(explode("/", filter_input(INPUT_SERVER, "REQUEST_URI"))));
        
        $pre = array_intersect($root_array, $uri_array);
        
        for ($i = 0; $i < count($pre); $i++) array_shift($uri_array);
        
        if (in_array(":json", $uri_array)) {
            $this->return_json = true;
            unset($uri_array[array_search(":json", $uri_array)]);
        }
        
        $root = array_merge($root_array, $uri_array);
        
        return "/".implode("/", $root);
    }
    
    
    /**
     * Checks to see if a file is valid or not. PHP = not valid. Everything
     * else is. Includes the contents of the file when it's not a php file.
     * 
     * @return
     */
    private function check_requested_file() {
        $uri = explode("?", $this->get_uri());
        $paths = explode("/", $uri[0]);
        $file_name = explode(".", end($paths));
        
        $file = $this->Theamus->file_path($uri[0]);
        
        if (!is_dir($file) && !file_exists($file)) return;
        elseif (end($file_name) == "php") {
            $this->error_page(404);
            exit();
        } else {
            if (!is_dir($file) && file_exists($file)) {
                header("Content-Type:".$this->get_content_type(end($file_name), $file));
                header("Content-Length:".filesize($file));

                $handle = fopen($file, 'rb');
                while (!feof($handle)) {
                  echo fread($handle, (($this->Theamus->get_server_memory_limit() / 2) / 2));
                  ob_flush();
                  flush();
                }
                fclose($handle);
                exit();
            }
        }
    }
    
    
    /**
     * Gets the content type of a file. There are some specific oddities to
     * the way PHP gets them, so this is to clear that up.
     * 
     * (example: CSS content type in PHP eyes is plain/text and this turns it
     * into text/css)
     * 
     * @param string $file
     * @return string
     */
    private function get_content_type($extension, $filepath) {
        $content_type = mime_content_type($filepath);

        switch ($extension) {
            case "css": $content_type = "text/css"; break;
            case "js": $content_type = "text/javascript"; break;
            case "eot": $content_type = "application/vnd.ms-fontobject"; break;
            case "otf": case "ttf": $content_type = "application/font-sfnt"; break;
            case "woff": $content_type = "application/font-woff"; break;
        }
        
        return $content_type;
    }


    /**
     * Initiates class variables and some site configuration settings
     *
     * @return
     */
    private function initiate($params) {
        $this->clean_global_variables();

        // Display errors, if desired by the site admin
        $this->display_errors();

        // Define the parameters given by the URL
        $this->parameters = $this->define_parameters($params);

        return; // Return!
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
            // Define the feature folder
            $this->feature_folder = $this->define_feature();

            // Load the feature configuration information
            $this->feature_configuration();

            // Load the functions that were defined in the configuration file
            $this->load_config_functions();

            // Define the folders to the file
            $this->look_in_folder = $this->look_in_folder($call['look_folder']);
            $this->feature_path_folders = $this->define_feature_folders();

            // Define the feature file and the path to the feature file
            $this->define_feature_file();

            // Define the feature file information
            $this->feature['files'] = $this->feature_files_configuration();

            return true; // Good return!
        }

        return false; // Bad return :(
    }


    /**
     * Displays errors for PHP based on the site configuration
     *
     * @return boolean
     */
    private function display_errors() {
        ini_set("display_errors", 0); // Don't show nothing! (default)

        // Check the system settings for whether or not to show errors
        if (isset($this->Theamus->settings['display_errors'])) {
            // Show errors based on the settings
            ini_set("display_errors", $this->Theamus->settings['display_errors']);
        }
    }


    /**
     * Determines whether or not developer mode is turned on
     *
     * @return boolean
     */
    private function developer_mode() {
        // Underused, but pretty neat!
        return isset($this->Theamus->settings['developer_mode']) && $this->Theamus->settings['developer_mode'] == "1" ? true : false;
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
               if ($hash['key'] != $this->Theamus->API->get_hash() && $this->api == true && $this->api_from == false) {
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

        $this->ajax = true;
        $ajax = $api_from = false;
        if (isset($post['ajax'])) $ajax = $post['ajax'];
        if (isset($get['ajax']) && $ajax == false) $ajax = $get['ajax'];
        $this->ajax = $ajax;

        if (!isset($post['ajax']) && !isset($get['ajax'])) {
            if ($this->Theamus->DB->try_installer) {
                require $this->Theamus->file_path(ROOT."/system/install.class.php");
                $install = new Install($this->Theamus, $this->Theamus->base_url);
                $installed = $install->run_installer();

                $this->install = $installed ? false : true;
            }

            $ret['type'] = "regular";
            $ret['look_folder'] = "view";
            $ret['do_call'] = "show_page";
        } else {
            if ($ajax == false) throw new Exception("AJAX type cannot be found.");

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
                case "instance":
                    $ret['type'] = "instance";
                    $ret['look_folder'] = "";
                    $ret['do_call'] = "run_instance";
                    break;
                default: false;
            }

            $this->api_from = $api_from;
        }
        return $ret;
    }
    
    
    /**
     * Returns the type of call that was made (e.g. ajax, instance api)
     * 
     * @return string|boolean $this->ajax
     */
    public function get_call_type() {
        return $this->ajax;
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
        
        if (in_array(":json", $ret)) {
            $this->return_json = true;
            unset($ret[array_search(":json", $ret)]);
        }
        
        return array_values($ret);
    }


    /**
     * Determines whether or not the user is trying to go to a page that exists
     * in the database
     *
     * @return boolean
     */
    private function determine_page() {
        // Query the database for a page with the alias in the parameters
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table("pages"),
            array(),
            array("operator" => "",
                "conditions" => array("alias" => $this->parameters[0])));

        // Check for errors in the query
        if (!$query) return false;

        // Check for any results
        if ($this->Theamus->DB->count_rows($query) == 0) return false;

        // Define the page data from the database
        $page = $this->Theamus->DB->fetch_rows($query);

        // Check if a user has permission to view the page or not
        foreach (explode(',', $page['groups']) as $group) {
            if (!$this->Theamus->User->user) $this->Theamus->User->send_to_login();
            if (!$this->Theamus->User->in_group($group)) die($this->error_page());
        }

        // Define the theme and theme file desired for the page
        $theme = explode(":", $page['theme']);
        $file = $theme[count($theme)-1];

        // Check for page navigation
        if ($page['navigation'] == "") $navigation = "";
        else {
            $navigation = array(); // Define empty navigation variable to add to

            // Loop through all of the navigation links from the database
            foreach(explode(",", $page['navigation']) as $n) {
                $link = explode("::", $n); // Define the link information as an array

                // Add the link to the navigation array
                $navigation[$link[0]] = isset($link[1]) ? $link[1] : "";
            }
        }

        // Define the page alias, from the URL
        $this->page_alias = $this->parameters[0];

        // Define the pages 'feature' information
        $this->feature['files']['theme']    = $file;
        $this->feature['files']['nav']      = $navigation;
        $this->feature['files']['header']   = $page['title'];
        $this->feature['files']['title']    = $page['title'];

        return true; // Return!
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
            $feature_path = $this->Theamus->file_path(ROOT."/features/$feature_folder");
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
            $folder_path = $this->Theamus->file_path(ROOT."/features/$this->feature_folder$this->look_in_folder");
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
     * Defines the index file for a folder that's being called for.
     * Checks to make sure, applies defaults where necessary.
     *
     * @param string $file_name
     */
    public function set_folder_index($file_name = "") {
        if ($file_name == "") $file_name = "index";

        $exploded = array_filter(explode(".", $file_name));
        $extension = end($exploded);

        if ($extension != "php") $file_name = "{$file_name}.php";

        $path = $this->Theamus->file_path(ROOT."/features/{$this->feature_folder}{$this->look_in_folder}");
        if ($this->feature_path_folders != false) $path .= $this->feature_path_folders;

        if (!file_exists($path.$file_name)) $file_name = "__.php";

        $this->folder_index = substr($file_name, 0, -4);
    }


    /**
     * Returns the defined/default folder index file
     *
     * @return string
     */
    public function get_folder_index() {
        if ($this->folder_index == null) $this->set_folder_index();
        return $this->folder_index;
    }


    /**
     * Sets a folder to imply the index, allowing parameters to be directly passed
     * to it without directly calling it.
     *
     * @param string $folder
     */
    public function imply_folder_index($folder = "") {
        $folders = array_filter(explode("/", $this->feature_path_folders));
        if ((empty($folders) && $folder == "root") || (end($folders) == $folder)) {
            array_splice($this->parameters, 0, 0, array($this->get_folder_index()));
            $this->folder_index_is_implied = true;
            $this->shift_parameters = true;
            $this->check_file_existence();
        }
    }


    /**
     * Checks to see if a folder's index should be implied or not.
     *
     * @return boolean
     */
    public function folder_index_implied() {
        if ($this->folder_index_is_implied == null) return false;
        return $this->folder_index_is_implied;
    }


    /**
     * Recursively checks to see if a file exists and whether or not to imply the
     * index file for the folder
     *
     * @param boolean $try_index
     * @return boolean
     */
    public function check_file_existence($try_index = false) {
        if ($this->page == true) {
            $file_name = "show-page.php";
        } elseif (isset($this->parameters[0])) {
            $file_name = "{$this->parameters[0]}.php";
        } elseif ($this->folder_index != NULL) {
            $file_name = $this->get_folder_index().".php";
        } elseif ($this->folder_index_implied() && $try_index == true) {
            $file_name = $this->get_folder_index().".php";
        } elseif ($this->feature_file != NULL && $this->folder_index == NULL && !$this->folder_index_implied()) {
            $file_name = $this->feature_file;
        } else {
            $file_name = "index.php";
        }

        $path = ROOT."/features/{$this->feature_folder}{$this->look_in_folder}";
        if ($this->feature_path_folders != false) $path .= $this->feature_path_folders;
        $path .= $file_name;
        $path = $this->Theamus->file_path($path);

        if (file_exists($path)) {
            $this->feature_file = $file_name;
            $this->complete_file_path = $path;
            return true;
        } elseif (!file_exists($path) && $try_index == true) {
            return false;
        } else {
            return $this->check_file_existence(true);
        }
    }


    /**
     * Defines the file that was called and the entire file path to include later on
     *
     * @return array|boolean $ret
     */
    private function define_feature_file() {
        if ($this->feature_folder != false) {
            if (!$this->check_file_existence()) return false;
        }
        return false;
    }


    /**
     * Returns the name (optionally, including path of folders) of the file
     * that has been requested
     *
     * @param boolean $file_only
     * @return string
     */
    public function get_called_file($file_only = false) {
        if (!$this->check_file_existence() && !$this->folder_index_implied()) {
            $this->handle_issues();
        } elseif ($this->check_file_existence() && $this->folder_index_implied() && $this->feature_file != NULL) {
            if ($this->shift_parameters && $this->folder_index_implied()) {
                array_shift($this->parameters);
                $this->shift_parameters = false;
            }

            if ($file_only) return $this->feature_file;
            else return $this->feature_path_folders.$this->feature_file;
        } elseif ($this->feature_file == NULL && $this->folder_index != NULL) {
            if ($file_only) return $this->folder_index;
            else return $this->feature_path_folders.$this->folder_index;
        } else {

            if ($file_only) return $this->feature_file;
            else return $this->feature_path_folders.$this->feature_file;
        }
    }


    /**
     * Handles all issues that accumulated during the defining of the feature's
     * path.
     *
     * @return boolean
     */
    private function handle_issues() {
        $message = false;

        if (!isset($this->feature['config']) || empty($this->feature['files'])
            || $this->feature['config'] == false) {
            $message = 1;
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
                "table"     => $this->Theamus->DB->system_table("features"),
                "clause"    => array(
                    "operator"  => "",
                    "conditions"=> array("alias" => $this->feature_folder)
                )
            );
            $query = $this->Theamus->DB->select_from_table($query_data['table'], array(), $query_data['clause']);

            if ($query) {
                $results = $this->Theamus->DB->fetch_rows($query);

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
     * Strips spaces from a block of HTML code. From a new line to the next different
     * character, all of the spaces will be removed.
     * 
     * @param string $content
     * @return string
     */
    private function strip_spaces($content = "") {
        if ($content == "") return "";
        return preg_replace("/(\r\n)[ ]*/", " ", $content);
    }
    
    
    /**
     * Extracts the script from a block of HTML code to be loaded separately
     * from the HTML.
     * 
     * @param string $content
     * @return array $scripts
     */
    private function extract_scripts($content = "") {
        if ($content == "") return array();
        preg_match_all("/<script\b[^>]*>([\s\S]*?)<\/script>/", $content, $matches);
        $scripts = array();
        foreach ($matches[1] as $script) $scripts[] = JSMin::minify($script);
        return $scripts;
    }
    
    
    /**
     * Cleans the JSON return data. Strips slashes, and removes the script blocks
     * from the HTML code
     * 
     * @param string $content
     * @return string
     */
    private function clean_json($content = "") {
        if ($content == "") return "";
        $content = $this->strip_spaces($content);
        preg_match_all("/<script\b[^>]*>([\s\S]*?)<\/script>/", $content, $matches);
        foreach ($matches[0] as $script) $content = str_replace($script, "", $content);
        return $content;
    }


    /**
     * Shows content with the site's theme surrounding it
     *
     * @return
     */
    private function show_page() {
        $feature_info = $this->get_feature_information();

        foreach (explode(',', $feature_info['groups']) as $group) {
            $in_group[] = $this->Theamus->User->in_group($group) ? true : false;
        }

        if (!in_array(true, $in_group) && $this->Theamus->User->user == false) {
            $this->Theamus->User->send_to_login();
        } elseif (!in_array(true, $in_group) && $this->Theamus->User != false) {
            die($this->error_page());
        }
        if ($feature_info['enabled'] == 0) die($this->error_page());

        $this->Theamus->User->set_420hash();

        if ($this->install == false) {
            $settings = $this->Theamus->settings;
        } else {
            $settings['name'] = "Theamus Installation";
        }

        $this->load_class_legacy();
        $data = $this->define_theme_data($settings['name']);

        if ($this->return_json) {
            $json = array();
            
            $Theamus = $this->Theamus;
            
            ob_start();
            include($data['file_path']);
            $json['content'] = ob_get_contents();
            ob_end_clean();
            
            $json['scripts'] = $this->extract_scripts($json['content']);
            $json['content'] = $this->clean_json($json['content']);
            
            header('Content-Type: application/json');
            exit(json_encode($json));
        } else {
            if (!empty($this->parameters)) {
                if ($this->parameters[0].".php" == $this->get_called_file(true)) array_shift($this->parameters);
            }

            unset($settings);
            $this->Theamus->Theme->load_theme($data);
            return;
        }
    }


    /**
     * Defines data that will be sent to the theme when the page loads
     *
     * @param string $name
     * @return array $data
     */
    private function define_theme_data($name) {
        $data['name']           = $name;
        $data['base']           = $this->Theamus->base_url;
        $data['css']            = $this->get_css();
        $data['js']             = $this->get_javascript();
        $data['title']          = @$this->feature['files']['title'];
        $data['header']         = @$this->feature['files']['header'];
        $data['feature']        = $this->feature_folder;
        $data['nav']            = isset($this->feature['files']['nav']) ? $this->feature['files']['nav'] : "";
        $data['admin']          = $this->Theamus->User->user && $this->Theamus->User->is_admin() ? $this->include_admin() : "";
        $data['theme']          = $this->define_theme_path();
        $data['template']       = isset($this->feature['files']['theme']) ? $this->feature['files']['theme'] : "default";
        $data['file_path']      = $this->complete_file_path;
        $data['page_alias']     = $this->page_alias;
        $data['url_params']     = $this->parameters;
        $data['no_database']    = $this->install;
        $data['init_classes']   = $this->init_classes;

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
            $Theamus = $this->Theamus;

            $folder = "default";
            $query_data = array(
                "table" => $this->Theamus->DB->system_table("themes"),
                "clause"=> array(
                    "operator"  => "",
                    "conditions"=> array("active" => 1)
                )
            );
            $query = $this->Theamus->DB->select_from_table($query_data['table'], array(), $query_data['clause']);

            if ($this->Theamus->DB->count_rows($query) > 0) {
                $results = $this->Theamus->DB->fetch_rows($query);
                $folder = $results['alias'];
            }

            $path = $this->Theamus->file_path(ROOT."/themes/$folder/");
        } else {
            $path = $this->Theamus->file_path(ROOT."/themes/installer/");
        }

        return $path;
    }


    /**
     * Defines the feature configuration settings into an array
     *
     * @return array
     */
    private function feature_configuration() {
        $Theamus = $this->Theamus;

        $feature_path = $this->Theamus->file_path(ROOT."/features/$this->feature_folder/");

        if (file_exists($feature_path."config.php")) {
            include $feature_path."config.php";
        }
    }


    /**
     * Sets the configuration information for a feature
     *
     * @param array $config
     * @return
     */
    public function set_feature_config($config = array()) {
        $this->feature['config'] = $config;
        return;
    }


    /**
     * Takes the given information from the config.php file for the loading feature
     *  and loads any miscellaneous function files before files.info.php loads
     *
     * @param array $config
     * @return
     */
    private function load_config_functions() {
        $feature_path = $this->Theamus->file_path(ROOT."/features/$this->feature_folder/");
        if (isset($this->feature['config']['load_files']) && isset($this->feature['config']['load_files']['function'])) {
            $arr = !is_array($this->feature['config']['load_files']['function']) ? array($this->feature['config']['load_files']['function']) : $this->feature['config']['load_files']['function'];
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
        $Theamus = $this->Theamus;

        $feature_path = $this->Theamus->file_path(ROOT."/features/$this->feature_folder/");
        $folders = array_filter(explode("/", $this->feature_path_folders));
        $location = urldecode(filter_input(INPUT_POST, "location"));
        $post_ajax = filter_input(INPUT_POST, "ajax");
        $get_ajax = filter_input(INPUT_GET, "ajax");
        $ajax = $this->ajax;

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
     * Defines the custom javascript or css folder that is used by the feature in order to
     * call custom files
     *
     * This function goes hand-in-hand with get_javascript() and get_css()
     *
     * @return string|boolean
     */
    private function get_custom_folder($type) {
        $config = isset($this->feature['config']) ? $this->feature['config'] : array();

        if (isset($config['custom_folders']) && is_array($config['custom_folders'])) {
            if (array_key_exists($type, $config['custom_folders'])) {
                $path = $this->Theamus->file_path(ROOT."/features/".$this->feature_folder."/".$config['custom_folders'][$type]);
                if (is_dir($path)) return $config['custom_folders'][$type];
            }
        }
        if (is_dir($this->Theamus->file_path(ROOT."/".$this->feature_folder."/js"))) return $type;
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
        if (!isset($this->feature['files'])) return array();
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
        );
        $this->get_card_css($ret);
        return implode("", $ret);
    }


    /**
     * Adds the Cards css to the DOM
     *
     * @param array $given
     */
    private function get_card_css(&$given) {
        if ($this->developer_mode()) {
            $given[] = "<link rel='stylesheet' href='system/styles/css/dev/cards.css'>";
        } else {
            $given[] = "<link rel='stylesheet' href='system/styles/css/cards.min.css'>";
        }
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

        $folder = $this->get_custom_folder("javascript");
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
            "<script src='".($this->developer_mode() ? "system/js/dev/ajax.js" : "system/js/ajax.min.js")."'></script>",
            "<script src='".($this->developer_mode() ? "system/js/dev/main.js" : "system/js/main.min.js")."'></script>",
            "<script src='system/js/theamus.js'></script>",
            "<script src='".($this->developer_mode() ? "system/js/dev/instance.js" : "system/js/instance.min.js")."'></script>",
            "<script src='system/external/prettify/prettify.js'></script>",
            $this->Theamus->User->user && $this->Theamus->User->is_admin() ?
                ($this->developer_mode() ? "<script src='themes/admin/js/admin.js'></script>" : "<script src='themes/admin/js/admin.min.js'></script>") : "",
            "<script>Theamus.info = ".$this->define_javascript_info()."</script>",
        );
        $this->get_card_js($ret);
        return implode("\n", $ret);
    }


    /**
     * Adds Theamus.Style.Cards to every pageload <3
     *
     * @param array $given
     */
    private function get_card_js(&$given) {
        if ($this->developer_mode()) {
            $given[] = "<script src='system/js/dev/cards/Style.js'></script>";
            $given[] = "<script src='system/js/dev/cards/Cards.js'></script>";
            $given[] = "<script src='system/js/dev/cards/Collapsible.js'></script>";
            $given[] = "<script src='system/js/dev/cards/Content.js'></script>";
            $given[] = "<script src='system/js/dev/cards/Expansion.js'></script>";
            $given[] = "<script src='system/js/dev/cards/Input.js'></script>";
            $given[] = "<script src='system/js/dev/cards/Progress.js'></script>";
        } else {
            $given[] = "<script src='system/js/cards.min.js'></script>";
        }
        $given[] = "<script>Theamus.Style.loadCards();</script>";
    }


    /**
     * Defines information to be passed to javasript on page load
     *
     * @return json $info
     */
    private function define_javascript_info() {
        $info = array(
            "site_base"    => $this->Theamus->base_url,
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
        if (!isset($this->feature['files'])) return array();
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
     * Loads a class file for a page call
     *
     * @param string $file
     * @param string $name
     * @param string $var
     * @return
     * @throws Exception
     */
    protected function load_class($file = "", $name = "", $var = "") {
        if ($file == "") throw new Exception("Failed to load a class because no class file was defined.");
        if ($name == "") throw new Exception("Failed to load a class because no class name was defined.");
        if ($var == "") $var = $name;

        $class_folder = $this->get_class_folder();
        $folder = ROOT."/features/{$this->feature_folder}/".($class_folder == "" ? "" : $class_folder."/");
        $file_path = $this->Theamus->file_path($folder.$file);

        if (!file_exists($file_path)) {
            throw new Exception("Failed to load a class because the class file was not found or does not exist.");
        } else include $file_path;

        $this->init_classes[] = array($name, $var);
        return;
    }


    /**
     * The -old- way of doing things. Ugh.  This will be deprecated in 1.5.0
     *
     * FROM:
     * $feature['class']['file'] = "x"
     * $feature['class']['init'] = "xy"
     * $xy = new xy($Theamus);
     *
     * TO:
     * $Theamus->Call->load_class(x, xy)
     * $xy = new xy($Theamus);
     *
     * @return type
     */
    protected function load_class_legacy() {
        $files = $this->feature['files'];

        if (!is_array($files) && !isset($files['class'])) return;
        if (!isset($files['class']['file']) || !isset($files['class']['init'])) return;

        $this->load_class($files['class']['file'], $files['class']['init']);
        return;
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
            if (array_key_exists("custom_folders", $config)) {
                if (array_key_exists("class", $config['custom_folders'])) {
                    return $config['custom_folders']['class'];
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
    private function error_page($type = 404) {
        // Define the settings to use
        $settings['name'] = $this->install == false ? $this->Theamus->settings['name'] : "Theamus Installation";

        http_response_code($type);
        
        if ($this->get_call_type() != false) return;
        
        // Define the theme data
        $data['name']       = $settings['name'];
        $data['base']       = $this->Theamus->base_url;
        $data['title']      = $data['header'] = "Error";
        $data['theme']      = $this->define_theme_path();
        $data['template']   = "error";
        $data['error_type'] = $type;
        $data['nav']        = false;
        $data['css']        = $this->get_css();
        $data['js']         = $this->get_javascript();

        // Load the page
        $this->Theamus->Theme->load_theme($data);
        return;
    }


    /**
     * Defines the path to the administration theme/functionality/panel
     *
     * @return string
     */
    private function include_admin() {
        return $this->Theamus->file_path(ROOT."/themes/admin/html.min.php");
    }


    /**
     * Runs a PHP script that was called via AJAX
     *
     * @return boolean
     */
    private function do_ajax() {
        $Theamus = $this->Theamus;
        $this->load_class_legacy();

        if (is_array($this->init_classes) && !empty($this->init_classes)) {
            foreach ($this->init_classes as $class) {
                if (count($class) < 2) continue;
                ${$class[1]} = new $class[0]($this->Theamus);
            }
        }

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
        $Theamus = $this->Theamus;
        $this->load_class_legacy();

        if (is_array($this->init_classes) && !empty($this->init_classes)) {
            foreach ($this->init_classes as $class) {
                if (count($class) < 2) continue;
                ${$class[1]} = new $class[0]($this->Theamus);
            }
        }

        echo $this->get_javascript(true);
        echo $this->get_css(true);

        if (!empty($this->parameters)) {
            if ($this->parameters[0].".php" == $this->get_called_file(true)) array_shift($this->parameters);
        }

        include $this->complete_file_path;

        return true;
    }


    /**
     * Includes a file that was called via AJAX that lives in the system folder
     *
     * @return boolean
     */
    private function include_system_page() {
        $Theamus = $this->Theamus;

        $pre_slash = "";
        if (substr(ROOT, 0, 1) == "/") {
            $pre_slash = "/";
        }

        $desired = filter_input(INPUT_GET, "params");
        $path = $this->Theamus->file_path($pre_slash.trim(ROOT."/system/$desired", "/").".php");

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
        $Theamus = $this->Theamus;

        $path = $file.".php";
        if ($absolute == false) {
            $path = ROOT . "/features/";
            $path .= $feature == false ? $this->feature_folder : $feature;
            $path .= "/views/".$file.".php";
        }

        if (file_exists($this->Theamus->file_path($path))) include $this->Theamus->file_path($path);
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
                if ($this->Theamus->API->string_is_json(urldecode($value)) && !$this->Theamus->API->string_is_date(urldecode($value))) {
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
        // JSON request? Let another function deal with that.
        if ($this->return_json) $this->show_page();
        
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
                if (isset($this->feature['config']['load_files']['api'])) {
                    // If the class file isn't already an array, make it one
                    $feature_class_files = $this->feature['config']['load_files']['api'];
                    if (!is_array($feature_class_files)) {
                        $class_files = array($feature_class_files);
                    } else {
                        $class_files = $feature_class_files;
                    }

                    // Loop through all of the class files, including them
                    foreach ($class_files as $cf) {
                        $class_file_path = $this->Theamus->file_path(ROOT."/features/".$this->feature_folder."/".$cf);
                        if (file_exists($class_file_path)) {
                            include_once $class_file_path;
                        } else {
                            $error = "The API class file based on the requested URL doesn't exist or could not be found.";
                        }
                    }

                    if (class_exists($inp['method_class']) && method_exists($inp['method_class'], $inp['method'])) {
                        $class = ${$inp['method_class']} = new $inp['method_class']($this->Theamus);
                        try { $response = call_user_func(array($class, $inp['method']), $function_variables); }
                        catch (Exception $e) { $error = $e->getMessage(); }
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
     * Runs an instance object from a javascript request
     *
     * See: ROOT/system/instance.class.php
     *
     * @return int
     */
    private function run_instance() {
        try {
            $instance = new Instance($this->Theamus);
            echo json_encode($instance->return_instance());
        } catch (Exception $ex) {
            $code = $ex->getCode() == 0 ? 1 : $ex->getCode();

            echo json_encode(array(
                    "error" => array(
                        "message" => $ex->getMessage(),
                        "status" => 1,
                        "code" => $code
                    ),
                    "response" => array(
                        "data" => array()
                    )
                ));
        }

        return 0;
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
    
    
    /**
     * Shows a little icon on every HTML load that you can hover over and get
     * load statistics
     * 
     * @return 
     */
    public function show_page_information() {
        $setting = $this->Theamus->settings['show_page_information'];
        
        $call = $this->get_call_type();
        
        if ($setting == "" || ($call != false && $call != "include")) return;
        
        $setting_array = json_decode($setting, true);
        
        if ($setting_array == NULL || empty($setting_array)) return;
        
        $info = array();
        if (in_array("load_time", $setting_array)) $info[] = $this->show_page_load_time();
        if (in_array("query_count", $setting_array)) $info[] = $this->show_page_query_count();
        
        $position = "fixed";
        if ($call == "include") $position = "absolute";
        
        echo "<style></style>";        
        echo "<style>.theamus_page-information:hover .theamus_page-information-container{padding:5px 15px !important;width:auto !important;}</style>";
        
        echo "<div class='theamus_page-information' style='height:22px;position:{$position};bottom:10px;right:10px;font-size:8pt;font-family:sans-serif;margin-top:1px;'>";
        echo "<svg version='1.1' style='margin-top: 6px' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='15px' height='10px' viewBox='0 0 10 10' xml:space='preserve'><rect x='1.5' y='5.984' fill='#414042' width='1' height='6'/><rect x='3.5' y='4' fill='#414042' width='1' height='8'/><rect x='5.5' y='2' fill='#414042' width='1' height='10'/></svg>";
        
        echo "<div class='theamus_page-information-container' style='float:left;margin-right:10px;box-shadow:0 0 5px #888;padding:0px;width:0px;overflow:hidden;white-space:nowrap;background:white;'>".implode(" | ", $info)."</div>";
        
        echo "</div>";
    }
    
    
    /**
     * Gets the page's load time for the load stats
     * 
     * @return string
     */
    public function show_page_load_time() {
        return "<strong>Page load time:</strong> ".round($this->Theamus->get_run_time(), 5)." seconds";
    }
    
    
    /**
     * Gets the page's query count for the load stats
     * 
     * @return string
     */
    public function show_page_query_count() {
        return "<strong>Queries ran:</strong> ".$this->Theamus->DB->get_query_count();
    }
    
    
    /**
     * Returns the alias of a page 
     * 
     * @return string $this->page_alias
     */
    public function get_page_alias() {
        return $this->page_alias;
    }
}