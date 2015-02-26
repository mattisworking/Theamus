<?php

/**
 * Instance - Theamus persistance object class
 * PHP Version 5.5.3
 * Version 1.4.0
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Matt Temet
 */

class Instance {
    protected $instance_data = array();
    protected $function_variables = array();
    protected $instance = array();
    protected $instance_id;
    protected $new_instance = true;
    protected $run_timer = 0;
    protected $Theamus;
    
    
    /**
     * Construct the Instance class, connecting to Theamus, initializing the
     * Instance session variable, defining the instance data, starting a
     * run timer and defining the default instance expiration time.
     * 
     * @param object $t
     * @return int
     */
    public function __construct($t) {
        $this->Theamus = $t;
        
        $this->setup_session();
        $this->define_data();
        $this->start_timer();
        
        $this->expire_time = 60 * 60 * 6;
        
        return 0;
    }
    
    
    /**
     * As the class closes, this will clean up any instances that need to be
     * cleaned up.
     * 
     * @return int
     */
    public function __destruct() {
        $this->clean_expired_instances();
        return 0;
    }
    
    
    /**
     * Defines the current time so the total run time can be tracked at any point
     * in a script's run.
     * 
     * @return float
     */
    private function start_timer() {
        $time = microtime(true);
        $this->run_timer = $time;
        return $time;
    }
    
    
    /**
     * Calculates the run time from the start time to the current time.
     * 
     * @return float
     */
    private function get_run_time() {
        return microtime(true) - $this->run_timer;
    }
    
    
    /**
     * Defines the session variables needed in order for Instances to be saved.
     * 
     * @return int
     */
    private function setup_session() {
        if (!isset($_SESSION['Theamus'])) $_SESSION['Theamus'] = array();
        if (!isset($_SESSION['Theamus']['Instances'])) $_SESSION['Theamus']['Instances'] = array();
        return 0;
    }
    
    
    /**
     * Returns the variables from a request that are related to the Theamus system
     * and not a part of the request data.
     * 
     * @return array
     */
    private function get_system_keys() {
        return array(
            "ajax-hash-data",
            "ajax",
            "instance_feature",
            "instance_class",
            "instance_function",
            "instance_id",
            "instance_delete",
            "instance_delete_all",
            "instance_reference",
            "instance_reference_all"
        );
    }
    
    
    /**
     * Defines data sent from the request (either GET or POST) and cleans out the
     * system key variables.
     * 
     * @throws Exception
     * @return int
     */
    private function define_data() {
        $data = array();
        
        $post = filter_input_array(INPUT_POST);
        if (!empty($post)) $data = $post;
        else {
            $get = filter_input_array(INPUT_GET);
            if (!empty($get)) $data = $get;
        }
        
        if (empty($data)) throw new Exception("Failed to get POST/GET data for the instance.", 1000);
        
        foreach ($data as $key => $val) {
            if ($val == "false") $data[$key] = false;
            if ($val == "true") $data[$key] = true;
        }
        
        foreach ($this->get_system_keys() as $key) {
            $this->instance_data[$key] = $data[$key];
            unset($data[$key]);
        }
        
        $this->function_variables = $data;
        $this->set_instance_id();
        
        return 0;
    }
    
    
    /**
     * Checks for the instance's feature folder in both the variable definition from
     * the request and the folder's physical existance.
     * 
     * @return string $feature_path
     * @throws Exception
     */
    private function get_feature_folder() {
        if (!isset($this->instance_data['instance_feature'])) {
            throw new Exception("No feature defined for the instance.", 1001);
        } else {
            $feature_path = $this->Theamus->file_path(ROOT."/features/{$this->instance_data['instance_feature']}/");
            if (!is_dir($feature_path)) {
                throw new Exception("Feature folder could not be found.", 1002);
            }
            
            return $feature_path;
        }
    }
    
    
    /**
     * Checks for the instance's feature class file in both the variable definition
     * from the request and the file's phyiscal existance.
     * 
     * @return type
     * @throws Exception
     */
    private function get_feature_class() {
        $config = $this->get_feature_folder()."/config.php";
        if (!file_exists($config)) {
            throw new Exception("Failed to get feature configuration to find the class file.", 1003);
        }
        
        $Theamus = $this->Theamus;
        $init = $this->Theamus->Call->feature['config'];
        
        include $config;
        
        $new = $this->Theamus->Call->feature['config'];
        
        $this->Theamus->Call->set_feature_config($init);
        
        if (!isset($new['load_files']['classes'])) {
            throw new Exception("Failed to find class file because no class files are defined in the feature config.", 1004);
        }
        
        if (!isset($new['load_files']['classes'][$this->instance_data['instance_class']])) {
            throw new Exception("Failed to find the class file for the class '{$this->instance_data['instance_class']}", 1005);
        }
        
        $class_file = $this->Theamus->file_path($this->get_feature_folder()."/".$new['load_files']['classes'][$this->instance_data['instance_class']]);
        
        if (!file_exists($class_file)) {
            throw new Exception("Failed to load the class file because the file defined doesn't exist or couldn't be found.", 1006);
        }
        
        return $class_file;
    }
    
    
    /**
     * Checks for the instance's requested class' existance
     * 
     * @throws Exception
     * @return int
     */
    private function get_class() {
        include $this->get_feature_class();
        if (!class_exists($this->instance_data['instance_class'])) {
            throw new Exception("Failed to load the class because the class doesn't exist or cound't be found.", 1007);
        }
        
        return 0;
    }
    
    
    /**
     * Defines the instance id from either a variable from the instance request, or
     * creates one based on the time of the request.
     * 
     * @return int $instanceid
     */
    private function set_instance_id() {
        if (is_numeric($this->instance_data['instance_id'])) {
            $instanceid = md5(time());
        } elseif ($this->instance_data['instance_id'] == "undefined") {
            throw new Exception("Failed to do anything with instances because of an 'undefined' instance id.", 1008);
        } else {
            $instanceid = $this->instance_data['instance_id'];
            $this->new_instance = false;
        }
        $this->instance_id = $instanceid;
        return $instanceid;
    }
    
    
    /**
     * Gets an object from an instance by either checking for one in the session variable,
     * or creating one and registering it as existing.
     * 
     * @return object
     */
    private function get_instance() {
        $this->get_class();
        
        if ($this->new_instance) {
            $ref = new ReflectionClass($this->instance_data['instance_class']);
            $instance = $ref->newInstanceArgs(array_values($this->function_variables));
            
            $_SESSION['Theamus']['Instances'][$this->instance_id] = array(
                "serial" => serialize($instance),
                "created" => time(),
                "updated" => 0,
                "delete"  => time() + $this->expire_time
            );
            
            return $instance;
        } else {
            if (!isset($_SESSION['Theamus']['Instances'][$this->instance_id]['serial'])) {
                throw new Exception("Failed to get instance object because none exists with the id of '{$this->instance_id}'", 1009);
            }
            
            return unserialize($_SESSION['Theamus']['Instances'][$this->instance_id]['serial']);
        }
    }
    
    
    /**
     * Checks for a function defined in the instance request variable then runs 
     * the function and returns the results.
     * 
     * @return function return
     */
    private function run_instance() {
        if ($this->instance_data['instance_function'] == "") {
            $this->get_instance();
            return true;
        } else {
            $instance = $this->get_instance();
            
            if (!is_object($instance)) {
                throw new Exception("Failed to run the instance because of a failed object creation.", 1010);
            }
            
            $result = call_user_func_array(
                array($instance, $this->instance_data['instance_function']),
                $this->function_variables
            );

            $_SESSION['Theamus']['Instances'][$this->instance_id] = array(
                "serial"  => serialize($instance),
                "created" => $_SESSION['Theamus']['Instances'][$this->instance_id]['created'],
                "updated" => time(),
                "delete"  => time() + $this->expire_time
            );

            return $result;
        }
    }
    
    
    /**
     * Deletes instance objects from the session variable when their time has expired.
     * 
     * @return int
     */
    public function clean_expired_instances() {
        $instances = $_SESSION['Theamus']['Instances'];
        
        foreach ($instances as $id => $instance) {
            if (time() > $instance['delete']) {
                unset($_SESSION['Theamus']['Instances'][$id]);
            } else continue;
        }
        
        return 0;
    }
    
    
    /**
     * Deletes a saved instance from the session variable
     * 
     * @return array
     */
    public function delete_instance() {
        $result = "";
        
        if (!isset($this->instance_data['instance_id']) || $this->instance_data['instance_id'] == "") {
            $result = false;
        } else {
            unset($_SESSION['Theamus']['Instances'][$this->instance_data['instance_id']]);
            $result = true;
        }
        
        return array("instance_result" => $result,
            "execution_time" => $this->get_run_time());
    }
    
    
    /**
     * Deletes all saved instances from the session variable
     * 
     * @return array
     */
    public function delete_all_instances() {
        $instances = $_SESSION['Theamus']['Instances'];
        
        foreach (array_keys($instances) as $id) {
            unset($_SESSION['Theamus']['Instances'][$id]);
        }
        
        return array("instance_result" => "All instances deleted.",
            "execution_time" => $this->get_run_time());
    }
    
    
    /**
     * Gathers the information from a saved instance and returns the serialized
     * object
     * 
     * @return array
     */
    public function reference_instance() {
        $result = "";
        
        if (!isset($this->instance_data['instance_id']) || $this->instance_data['instance_id'] == "") {
            $result = false;
        } else {
            if (isset($_SESSION['Theamus']['Instances'][$this->instance_data['instance_id']])) {
                $result = $_SESSION['Theamus']['Instances'][$this->instance_data['instance_id']];
            } else {
                $result = false;
            }
        }
        
        return array("instance_result" => $result,
            "execution_time" => $this->get_run_time());
    }
    
    
    /**
     * Returns all of the saved instances in the session variable
     * 
     * @return array
     */
    public function reference_all_instances() {
        $instances = $_SESSION['Theamus']['Instances'];
        if (count($instances) < 2) $instances = array($instances);
        
        return array("instance_result" => $instances,
            "execution_time" => $this->get_run_time());
    }
    
    
    /**
     * Returns all of the instance information when a request is made.
     * 
     * @return array
     */
    public function return_instance() {
        if ($this->instance_data['instance_delete'] == 1) {
            return $this->delete_instance();
        }
        
        if ($this->instance_data['instance_delete_all'] == 1) {
            return $this->delete_all_instances();
        }
        
        if ($this->instance_data['instance_reference'] == 1) {
            return $this->reference_instance();
        }
        
        if ($this->instance_data['instance_reference_all'] == 1) {
            return $this->reference_all_instances();
        }
        
        return array(
            "instance_result"   => $this->run_instance(),
            "instance_id"       => $this->instance_id,
            "execution_time"    => $this->get_run_time()
        );
    }
}