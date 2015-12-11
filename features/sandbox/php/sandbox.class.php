<?php

class Sandbox {
    private $Theamus;
    
    /**
     *  Hello Theamus.
     */
    public function __construct($t) { $this->Theamus = $t; }
    
    
    /**
     * Checks to see if the current system is in developer mode. If not, throws an exception.
     * 
     * @throws Exception
     */
    private function check_developer_mode() {
        if ($this->Theamus->settings['developer_mode'] == 0) {
            throw new Exception("You don't have permission to do that. Try turning on developer mode first.");
        }
    }


    /**
     * Gets a listing of installed features from the database.function
     * 
     * @returns boolean|array of [0]filename [1]feature name
     */
    public function get_features() {
        $this->check_developer_mode();
        
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table("features"),
            array("alias", "name"),
            array("operator" => "",
                "conditions" => array("[!]alias" => "sandbox")),
            "ORDER BY `name` ASC");
            
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            return false;
        }
        
        $results = $this->Theamus->DB->fetch_rows($query);
        return isset($results[0]) ? $results : array($results);
    }


    /**
     * Gets all API classes and functions possible to run via an AJAX API call. 
     * 
     * @throws Exception for no feature defined
     * @throws Exception for feature not exisiting in the directory
     * @throws Exception for no feature configuration file
     * @returns array of array [0]class name [1]public class method
     */
    public function get_feature_functions($args = array()) {
        $this->check_developer_mode(); // only in dev mode we can do this 
        
        if (!isset($args['feature'])) {
            throw new Exception("No feature was defined, can't get possible functions.");
        }
        
        $feature = $this->Theamus->file_path(ROOT."/features/{$args['feature']}/");
        
        if (!is_dir($feature)) {
            throw new Exception("The feature folder provided doesn't exist.");
        }
        
        // load in config for the api files
        if (file_exists($feature."config.php")) {
            $original_config = $this->Theamus->Call->feature['config']; // save inital config
            
            $Theamus = $this->Theamus; // define the scope of Theamus for the included file
            include $feature."config.php";
            
            $config = $this->Theamus->Call->feature['config']; // get included config
            $this->Theamus->Call->feature['config'] = $original_config; // restore initial config
        } else {
            throw new Exception("No configuration file for the feature could be loaded.");
        }
        
        // check for exsiting api files
        if (count($config['load_files']['api']) == 0) return array();
        
        $original_classes = get_declared_classes();
        
        // load in all the api files
        foreach ($config['load_files']['api'] as $api_file) {
            if (file_exists($feature.$api_file)) {
                include $feature.$api_file;
            }
        }
        
        $classes = array_values(array_diff(get_declared_classes(), $original_classes));
        
        // format all of the public methods into a return array
        $return = array();
        foreach ($classes as $class) {
            foreach (get_class_methods($class) as $method) {
                if (strpos($method, "__") === 0) continue; // ignore magic methods
                $return[] = array($class, $method);
            }
        }
        
        return $return;
    }
}