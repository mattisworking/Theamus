<?php

class Features {
    public $developer_message = "";
    public $install_sql;
    public $uninstall_sql;
    public function __construct() {
        // Initialize class variables
        $this->initialize_variables();
    }

    public function __destruct() {
        // Disconnect from the database
        $this->tDataClass->disconnect();
    }

    private function initialize_variables() {
        // Define the data class and connect to the database
        $this->tDataClass           = new tData();
        $this->tData                = $this->tDataClass->connect();
        $this->tDataClass->prefix   = DB_PREFIX;

        // Define the file management class
        $this->tFiles = new tFiles();
    }

    private function get_upload_file() {
        if (!isset($_FILES['file'])) return false;

        // Define the file array and the file name
        $file = $_FILES['file'];
        $name_arr = explode(".", $file['name']);

        // Check the filetype and return
        if (end($name_arr) !== "zip") throw new Exception("Only ZIP files may be uploaded.");
        else return $file;
    }

    private function extract_temp_feature($f) {
        // Define the path
        $path = ROOT."/features/features/temp/";

        // Extract the uploaded file
        if ($this->tFiles->extract_zip(path($path.$f), path(trim($path.$f, ".zip")))) {
            return true;
        } else {
            $this->developer_message = "The uploaded file failed to extract because of a corrupted file or an interruption in the script's run.";
            throw new Exception("There was an issue extracting the theme");
        }
    }

    private function extract_feature($f, $c) {
        // Define the path
        $temp_path = path(ROOT."/features/features/temp/$f");
        $path = path(ROOT."/features/".$c['alias']);

        // Extract the uploaded file
        if ($this->tFiles->extract_zip($temp_path, $path)) {
            return true;
        } else {
            $this->developer_message = "The uploaded file failed to extract because of a corrupted file or an interruption in the script's run.";
            throw new Exception("There was an issue extracting the theme");
        }
    }


    private function check_structure($f) {
        // Define the path to the config file
        $path = ROOT."/features/features/temp/".trim($f, ".zip");

        // Check required files and folders
        $temp[] = file_exists(path($path."/config.php")) ? true : false;
        $temp[] = file_exists(path($path."/files.info.php")) ? true : false;
        $temp[] = is_dir(path($path."/views/")) ? true : false;

        // React to check, return
        if (in_array(false, $temp)) {
            $this->developer_message = "Make sure the [feature root]/config.php, [feature root]/files.info.php files exist, along with the [feature root]/views/ directory.  For more information on creating features, <a target='_blank' href='http://theamus.com/wiki/read/creating-features'>check out the documentation</a>.";
            throw new Exception("One or more requirements are missing in the feature file/folder structure.");
        }
    }

    private function check_config($f) {
        // Define the path
        $path = ROOT."/features/features/temp/".trim($f, ".zip");

        // Define the required variables
        $required = array("['scripts']['folder']", "['css']['folder']", "['js']['folder']", "['alias']", "['name']", "['groups']", "['db_prefix']");

        // Include the config file
        if (file_exists(path($path."/config.php"))) {
            include path($path."/config.php");
        } else {
            $this->developer_message = "Make sure the [feature root]/config.php file exists.  For more information on the feature configuration file, <a target='_blank' href='http://theamus.com/wiki/read/feature-configuration-file'>check out the documentation</a>.";
            throw new Exception("There is no feature configuration file. Aborting the upload.");
        }

        // Check the required variables
        foreach ($required as $item) {
            $var = "feature".$item;
            $temp[] = isset($var) ? true : false;
        }

        // Check for all valid requirements
        if (in_array(false, $temp)) {
            $this->developer_message = "A required variable for the conifguration file is not defined like it should be.  For more information on the feature configuration file, <a target='_blank' href='http://theamus.com/wiki/read/feature-configuration-file'>check out the documentation</a>.";
            throw new Exception("One or more requirements are missing in the feature configuration file.");
        }

        // Define the feature folder alias for the feature
        $this->feature_folder_alias = $feature['alias'];

        // Return
        return $feature;
    }

    private function upload_feature($upload = false) {
        // Check and define the file, the path, and the temp name
        if (count($_FILES) == 0 && $upload == false) {
            $this->developer_message = "Cannot upload nothing.";
            throw new Exception("Please select a file to upload.");
        }
        $file = $this->get_upload_file();
        $path = ROOT."/features/features/temp/";
        $temp_name = md5(time()).".zip";

        // Upload the file
        if (move_uploaded_file($file['tmp_name'], path($path.$temp_name))) {
            return $temp_name;
        } elseif ($file != false) {
            $this->developer_message = "The reason for this is most likely because the upload folder can't be written to.";
            throw new Exception("The file failed to upload.");
        }
    }

    private function gather_scripts($f, $config, $for = "install") {
        // Check for a script
        if (!isset($config[$for]['script'])) return false;

        // Define the script
        $path = path(ROOT."/features/features/temp/".trim($f, ".zip")."/".$config[$for]['script']);

        // Define the class
        include path(ROOT."/features/features/classes/install.class.php");
        $Features = $this;
        $Features->install = new FeatureInstall();
        $Features->install->config = $config;

        // Include the script
        if (file_exists($path)) include $path;

        // Define the SQL
        $this->install_sql = $Features->install->get_install_sql();
    }


    private function gather_remove_scripts($folder) {
        // Include the config file
        $config_path = path(ROOT."/features/$folder/config.php");
        if (file_exists($config_path)) include $config_path;

        // Check if we should bother going on or not
        if (isset($feature['remove']['script'])) {
            // Define the path to the script
            $path = path(ROOT."/features/$folder/".$feature['remove']['script']);

            // Define the install class
            include path(ROOT."/features/features/classes/uninstall.class.php");
            $Features = $this;
            $Features->uninstall = new FeatureUninstall();

            // Include the install script
            if (file_exists($path)) include $path;

            // Define the install SQL
            $this->uninstall_sql = $Features->uninstall->get_uninstall_sql();
        }
    }

    private function format_bytes($bytes) {
        // Define the units
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        // Do the math to get the new bytesize
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        // Return the bytesize
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function gather_information($f, $c) {
        // Define the filename of the upload
        $return['filename'] = $f;

        // Define the files in the folder
        $files = $this->tFiles->scan_folder(path(ROOT."/features/features/temp/".trim($f, ".zip")));

        // Define the counts of things that will change
        $return['files']        = count($files);
        $return['db_changes']   = count($this->install_sql);

        // Define the filesize that will be used
        $filesize = 0;
        foreach ($files as $file) $filesize += filesize($file);
        $return['filesize']     = $this->format_bytes($filesize);

        // Define the information given from the configuration file
        $return['alias']        = $c['alias'];
        $return['name']         = $c['name'];
        $return['version']      = isset($c['version']) ? $c['version'] : "";
        $return['notes']        = isset($c['notes']) ? $c['notes'] : "";

        // Define the author's information
        if (isset($c['author'])) {
            $return['author']['author']   = isset($c['author']['name']) ? $c['author']['name'] : "";
            $return['author']['alias']    = isset($c['author']['alias']) ? $c['author']['alias'] : "";
            $return['author']['email']    = isset($c['author']['email']) ? $c['author']['email'] : "";
            $return['author']['company']  = isset($c['author']['company']) ? $c['author']['company'] : "";
        }

        // Return the information
        return $return;
    }

    private function get_filename() {
        $filename = urldecode(filter_input(INPUT_POST, "filename"));
        if ($filename == "") {
            $this->developer_message = "The filename from the preliminary upload information wasn't defined properly and sent with the installation request.  Try re-installing the feature.";
            throw new Exception("There was an error finding the filename.");
        }
        return $filename;
    }

    private function install_database() {
        // Check for any queries to install
        if (!empty($this->install_sql)) {
            // Query the database, running the install queries
            $query = $this->tData->multi_query(implode("", $this->install_sql));

            // Check the query and flush the memory from the multi_query
            if (!$query) {
                $this->developer_message = "Database error information: ".print_r($this->tData->error, true);
                throw new Exception("There was an error running the database queries for this feature.");
            }
            while ($this->tData->next_result()) continue;
        }
    }

    private function uninstall_database() {
        // Check for any queries to run
        if (!empty($this->uninstall_sql)) {
            // Query the database, running the queries
            $query = $this->tData->multi_query(implode("", $this->uninstall_sql));

            // Check the query and flush the memory from the multi_query
            if (!$query) {
                $this->developer_message = "Database error information: ".print_r($this->tData->error, true);
                throw new Exception("There was an error running the database queries for this feature.");
            }
            while ($this->tData->next_result()) continue;
        }
    }

    private function install_database_feature($config = array()) {
        // Check the config argument
        if (empty($config) || !is_array($config)) {
            $this->developer_message = "The data from the configuration file was not attached to the proper variable.  Try re-installing the feature.";
            throw new Exception("Cannot install the feature in the database with this information.");
        }

        // Define the database friendly information
        $alias = $this->tData->real_escape_string($config['alias']);
        $name = $this->tData->real_escape_string($config['name']);
        $groups = $this->tData->real_escape_string(implode(",", $config['groups']));
        $prefix = $this->tData->real_escape_string($config['db_prefix']);

        // Query the database, installing the feature in the theamus features table
        $query = $this->tData->query("INSERT INTO `".$this->tDataClass->prefix."features` ".
                "(`alias`, `name`, `groups`, `permanent`, `enabled`, `db_prefix`) VALUES ".
                "('$alias', '$name', '$groups', 0, 1, '$prefix')");

        // Check the query
        if (!$query) {
            $this->developer_message = "Database error information: ".print_r($this->tData->error, true);
            throw new Exception("There was an error installing this feature in the database.");
        }
    }

    private function define_post_information($required = array()) {
        // Define the filtered post array
        $post = filter_input_array(INPUT_POST);

        // Check the values, adding the sanitized ones to the return array
        foreach ($required as $item) {
            if (!isset($post[$item]) || $post[$item] == "") throw new Exception("Please fill out the '$item' field.");
            $return[$item] = urldecode($post[$item]);
        }

        // Return the information
        return $return;
    }

    private function get_feature_information($id = 0) {
        // Query the database for the feature
        $query = $this->tData->query("SELECT * FROM `".$this->tDataClass->prefix."features` WHERE `id`='$id'");

        // Check the query and return
        if (!$query) {
            $this->developer_message = "Database error information: ".print_r($this->tData->error, true);
            throw new Exception("There was an error finding the feature information in the database.");
        }
        return $query->fetch_assoc();
    }

    private function remove_feature_tables($prefix = "") {
        // Check for a blank prefix
        if ($prefix == "") return;

        // Generate the queries to run
        $query = $this->tData->query("SHOW TABLES");

        // Check the query
        if (!$query) {
            $this->developer_message = "Database error information: ".print_r($this->tData->error, true);
            throw new Exception("There was an error finding all of the related database tables.");
        }
        if ($query->num_rows == 0) return;

        // Define the tables to remove
        $remove_tables = array();
        while ($table = $query->fetch_array()) {
            if (strpos($table[0], $prefix) !== false) {
                $remove_tables[] = "`".$table[0]."`";
            }
        }

        // Drop the related tables
        if (!empty($remove_tables)) {
            $this->tData->query("DROP TABLES ".implode(", ", $remove_tables).";");
        }
    }

    private function remove_relevant_data($alias = "") {
        // Check the alias, define a database-friendly variable
        if ($alias == "") {
            $this->developer_message = "In order to remove information relevant to this feature, the feature's alias must be defined, which means it can't be found in the database.";
            throw new Exception("The alias to remove related database information cannot be blank.");
        }
        $alias = $this->tData->real_escape_string($alias);

        // Delete the feature from the features table, check it too
        $query = $this->tData->query("DELETE FROM `".$this->tDataClass->prefix."features` WHERE `alias`='$alias'");
        if (!$query) {
            $this->developer_message = "Database error information: ".print_r($this->tData->error, true);
            throw new Exception("There was an error removing this feature from the database.");
        }

        // Delete the permissions from the database, check it
        $query = $this->tData->query("DELETE FROM `".$this->tDataClass->prefix."permissions` WHERE `feature`='$alias'");
        if (!$query) {
            $this->developer_message = "Database error information: ".print_r($this->tData->error, true);
            throw new Exception("There was an error removing the permissions associated to this feature from the database.");
        }
    }

    public function remove_feature_folder() {
        // Make sure the feature folder is defined, don't want to delete the feature folder
        if (isset($this->feature_folder_alias) && $this->feature_folder_alias != "") {
            // Define the path
            $path = path(ROOT."/features/".$this->feature_folder_alias);

            // Remove the folder
            $this->tFiles->remove_folder($path);
        }
    }

    public function clean_temp_folder() {
        // Define the path, all files and all folders
        $path = ROOT."/features/features/temp";
        $files = $this->tFiles->scan_folder($path);
        $folders = $this->tFiles->scan_folder($path, false, "folders");

        // Remove everything that isn't the blank file
        foreach ($files as $f) if ($f != path($path."/blank.txt")) unlink($f);
        foreach ($folders as $f) $this->tFiles->remove_folder($f);
    }

    public function prelim_install() {
        // Upload and extract the file
        $filename = $this->upload_feature();
        $this->extract_temp_feature($filename);

        // Perform checks
        $this->check_structure($filename);
        $config = $this->check_config($filename);

        // Gather information
        $this->gather_scripts($filename, $config, "install");
        return $this->gather_information($filename, $config);
    }

    public function install_feature() {
        // Define the filename
        $filename = $this->get_filename();

        // Define the config information and gather the install information
        $config = $this->check_config($filename);
        $this->gather_scripts($filename, $config, "install");

        // Extract the files to their location
        $this->extract_feature($filename, $config);

        // Attempt to install the database information
        $this->install_database_feature($config);
        $this->install_database();

        // Clean the temp folder
        $this->clean_temp_folder();
    }

    public function update_feature() {
        // Check for an uploaded file
        $filename = $this->upload_feature(true);

        // Check for an upload, extract the files
        if ($filename != false) {
            $this->extract_temp_feature($filename);

            // Define the config information and gather the update information
            $config = $this->check_config($filename);
            $this->gather_scripts($filename, $config, "update");

            // Extract the files to their location
            $this->extract_feature($filename, $config);

            // Attempt to run the update datbase information
            $this->install_database();

            // Clean the temp folder
            $this->clean_temp_folder();

            // Notify the user
            alert_notify("success", "This feature was updated successfully.");
        }
    }

    public function save_feature_information() {
        // Define the post information
        $post = $this->define_post_information(array("id", "groups", "enabled"));

        // Define sql-friendly database variables
        $id         = $this->tData->real_escape_string($post['id']);
        $groups     = $this->tData->real_escape_string($post['groups']);
        $enabled    = $post['enabled'] == "true" ? "1" : "0";

        // Query the database, saving the changes
        $query = $this->tData->query("UPDATE `".$this->tDataClass->prefix."features` SET `groups`='$groups', `enabled`='$enabled' WHERE `id`='$id'");

        // Check the query and return
        if (!$query) {
            $this->developer_message = "Database error information: ".print_r($this->tData->error, true);
            throw new Exception("There was an error saving this information.");
        }
        alert_notify("success", "This information has been saved.");
    }

    public function remove_feature() {
        // Define the post information
        $post = $this->define_post_information(array("feature_id"));

        // Get the feature information from the database and the config file
        $feature = $this->get_feature_information($post['feature_id']);
        $this->gather_remove_scripts($feature['alias']);

        // Remove any related database tables
        $this->remove_feature_tables($feature['db_prefix']);

        // Delete all auto-installed information and run the uninstall queries
        $this->remove_relevant_data($feature['alias']);
        $this->uninstall_database();

        // Delete the folder
        $this->tFiles->remove_folder(path(ROOT."/features/".$feature['alias']));

        // Notify the user
        alert_notify("success", "This feature has been removed.");
    }


    /**
     * Define the features tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function features_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('List of Features', 'index.php', 'Theamus Features'),
            array('Install a New Feature', 'install.php', 'Install a New Feature')
        );

        $return_tabs = array(); // Empty return array to add to

        // Loop through all of the tabs defined above and assign them to li items/links
        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : ''; // Define the current tab
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'features-tab\' data-file=\'features/'.trim($tab[1], '.php').'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        // Return the tabs to the page
        return '<ul>'.implode('', $return_tabs).'</ul>';
    }
}