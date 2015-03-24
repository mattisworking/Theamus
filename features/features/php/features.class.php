<?php

class Features {
    public $feature_config;


    /**
     * Connect to Theamus, check for administrators
     *
     * @param object $t
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;

        if (!$t->User->is_admin()) die('Only administrators have access to the Features feature.');

        return;
    }


    /**
     * Define the features tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function features_tabs($file = '') {
        $dev_tabs = array();
        if ($this->Theamus->settings['developer_mode'] == 1) {
            $dev_tabs[] = array("Create a Feature", "features/developer/create/php", "Create a Feature");
        }
        
        // Define the tabs and their options
        $tabs = array(
            array('List of Features', 'features/index.php', 'Theamus Features'),
            array('Install a New Feature', 'features/install.php', 'Install a New Feature'));
        
        if (count($dev_tabs) > 0) $tabs[] = array("More", $dev_tabs, "right");

        // Return the HTML tabs
        return $this->Theamus->Theme->generate_admin_tabs("features-tab", $tabs, $file);
    }


    /**
     * Cleans the features/temp folder for anything BUT the blank.txt file
     *
     * @return
     */
    public function clean_temp_folder() {
        // Define the path, all files and all folders
        $path = ROOT.'/features/features/temp';

        // Scan the temp folder for files
        $files = $this->Theamus->Files->scan_folder($path);

        // Scan the temp folder for folders
        $folders = $this->Theamus->Files->scan_folder($path, false, 'folders');

        // Remove everything that isn't the blank file
        foreach ($files as $f) if ($f != $this->Theamus->file_path($path.'/blank.txt')) unlink($f);
        foreach ($folders as $f) $this->Theamus->Files->remove_folder($f);

        return; // Return!
    }


    /**
     * Gets a feature from the database based on the ID or alias
     *
     * @param int $id
     * @param string $alias
     * @return type
     * @throws Exception
     */
    public function get_feature($id = 0, $alias = '') {
        $conditions = array(); // Initialize the conditions array

        // Define the conditions to look for an ID
        if ($id != 0 && is_numeric($id) && $id != '') $conditions['id'] = $id;

        // Define the conditions to look for an alias
        elseif ($alias != '') $conditions['alias'] = $alias;

        // Query the database for the feature
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('features'),
            array(),
            array('operator' => '',
                'conditions' => $conditions));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            throw new Exception('Failed to get feature information.');
        }

        // Return the query information
        return $this->Theamus->DB->fetch_rows($query);
    }


    /**
     * Gets all of the groups from the database and selects the ones that a
     * feature has permission to
     *
     * @param string $feature_groups
     * @return string
     */
    public function get_groups($feature_groups = '') {
        // Query the database for all of the groups
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('groups'));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            return '<option>Failed to get groups</option>';
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) return '<option>Could not find any groups.</option>';

        // Define the database information
        $results = $this->Theamus->DB->fetch_rows($query);
        $groups = isset($results[0]) ? $results : array($results);

        $options = array(); // Initialize the options array

        // Loop through all of the groups
        foreach ($groups as $group) {
            // Define the selected groups
            $selected = in_array($group['alias'], explode(',', $feature_groups)) ? 'selected' : '';

            // Define the options
            $options[] = '<option value="'.$group['alias'].'" '.$selected.'>'.$group['name'].'</option>';
        }

        return implode('', $options); // Return the options as a string
    }


    /**
     * Uploads a file from the temp PHP location to the temp feature location then
     * extracts it to the temp feature location
     *
     * @param string $tmp_name
     * @param string $file_path
     * @param string $folder_path
     * @return
     * @throws Exception
     */
    function upload_file($tmp_name = '', $file_path = '', $folder_path = '') {
        // Check for errors when uploading the ZIP file to the temp folder
        if (!move_uploaded_file($tmp_name, $file_path)) {
            $this->clean_temp_folder(); // Clean the temp folder

            // Log the permissions error
            $this->Theamus->Log->error('Failed to upload feature file.  Check folder permissions.');

            throw new Exception('Failed to upload the feature file.');
        }

        // Check for errors when trying to extract the uploaded zip file
        if (!$this->Theamus->Files->extract_zip($file_path, $folder_path)) {
            $this->clean_temp_folder(); // Clean the temp folder
            throw new Exception('Failed to extract the feature files.');
        }

        return; // Return!
    }


    /**
     * Includes the uploaded feature file to the script and gathers the information from it
     *
     * @param string $folder_path
     * @return
     * @throws Exception
     */
    public function get_check_config($folder_path = '', $info_only = false) {
        // Define the path to the configuration file
        $config_path = $this->Theamus->file_path($folder_path.'/config.php');

        // Check for the file's existance
        if (!file_exists($config_path)) {
            $this->clean_temp_folder(); // Clean the temp folder
            throw new Exception('Could not find the feature configuration file.');
        }

        // Define the call feature configuration information now before it gets erased
        $original_config = $this->Theamus->Call->feature['config'];

        // Define the theamus variable for the config file to use
        $Theamus = $this->Theamus;

        include $config_path; // Include the config file and let it run

        // Define the feature configuration information
        $this->feature_config = $this->Theamus->Call->feature['config'];

        // Restore the original configuration information
        $this->Theamus->Call->feature['config'] = $original_config;

        // Check for a defined folder name in the configuration file
        if (!isset($this->feature_config['folder_name'])) {
            $this->clean_temp_folder();
            throw new Exception('Failed to get the feature folder name from the configuration file.');
        }

        // Check for a define feature name in the configuration file
        if (!isset($this->feature_config['feature_name'])) {
            $this->clean_temp_folder();
            throw new Exception('Failed to get the feature name fomr the configuration file.');
        }

        // Only do this part if NOT looking for information
        if (!$info_only) {
            // Check if the feature has been installed to the DB once before
            if (count($this->get_feature(0, $this->feature_config['folder_name'])) > 0) {
                $this->clean_temp_folder();
                throw new Exception('A feature with the same configuration information been installed.');
            }

            // Define the feature path (ROOT/features)
            $feature_path = $this->Theamus->file_path(ROOT.'/features/'.$this->feature_config['folder_name']);

            // Check if there is already a folder in the features folder with the same name
            if (is_dir($feature_path)) {
                $this->clean_temp_folder();
                throw new Exception('A feature with the same file name has already been installed.');
            }
        }

        return; // Return!
    }


    /**
     * Checks the configuration file for scripts to run during the special run time
     *
     * @param string $folder_path
     * @param string $type
     * @param string $prefix
     * @return
     * @throws Exception
     */
    public function run_feature_scripts($folder_path = '', $type = '', $prefix = '') {
        // Check for the configuration scripts variable
        if (!isset($this->feature_config['configuration_scripts'])) return;

        // Check for the configuration script of the type being requested
        if (!isset($this->feature_config['configuration_scripts'][$type])) return;

        // Define the scripts for shorter variable names
        $scripts = $this->feature_config['configuration_scripts'][$type];

        // Check if the scripts are an array
        if (!is_array($scripts)) return;

        // Loop through all of the scripts defined
        foreach ($scripts as $script) {
            // Define the path to the script file
            $script_path = $this->Theamus->file_path($folder_path.'/'.$script);

            // Check if the file exists then include it
            if (file_exists($script_path)) include $script_path;
        }

        // Check for the proper function to run this time
        if (!function_exists($type)) return;

        // Run the function and check for errors
        if (!$type($this->Theamus, $prefix)) {
            $this->clean_temp_folder(); // Clean the temp folder

            // Don't commit to the database
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log any query errors

            throw new Exception('Failed to '.$type.' the feature because of the feature\'s '.$type.' script.');
        }
    }


    /**
     * Installs a feature to Theamus
     *
     * @return boolean
     * @throws Exception
     */
    public function install_feature() {
        // Check for an adminsitrator with the proper permissions
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('install_features')) {
            die('Only administrators with the proper permissions can do that');
        }

        // Check for files to be uploaded
        if (count($_FILES) == 0) throw new Exception('Choose a file to upload.');

        // Check for the correct files to be uploaded
        if (!isset($_FILES['file'])) throw new Exception('Failed to find the uploaded file.');

        $file = $_FILES['file']; // Shorter variable names

        // Define the extension and temporary name from the file uploaded
        $name_array = explode('.', $file['name']);
        $extension = $name_array[count($name_array) - 1];
        $temp_name = md5(time());

        // Define the path to the uploaded zip and the extracted folder
        $temp_file_path = $this->Theamus->file_path(ROOT.'/features/features/temp/'.$temp_name.'.'.$extension);
        $temp_folder_path = $this->Theamus->file_path(ROOT.'/features/features/temp/'.$temp_name);

        // Check the file type
        if ($extension !== 'zip') throw new Exception('Invalid file type.');

        // Upload the file to the temp folder
        $this->upload_file($file['tmp_name'], $temp_file_path, $temp_folder_path);

        // Check the configuration stuffs
        $this->get_check_config($temp_folder_path);

        // Define the table prefix that will be assigned to this feature
        $prefix = substr(md5(time()), 0, 6);

        // Transaction sql!
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);

        // Run the scripts associated to the feature
        $this->run_feature_scripts($temp_folder_path, 'install', $prefix);

        // Query the database, adding this feature to it
        $query = $this->Theamus->DB->insert_table_row(
            $this->Theamus->DB->system_table('features'),
            array('alias'   => $this->feature_config['folder_name'],
                'name'      => $this->feature_config['feature_name'],
                'groups'    => 'administrators',
                'permanent' => 0,
                'enabled'   => 1,
                'db_prefix' => $prefix));

        // Check the query for errors
        if (!$query) {
            $this->clean_temp_folder(); // Clean the temp folder

            // Roll back any changes
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to install the feature.');
        }

        // Check for errors when extracting the files to their final destination
        if (!$this->Theamus->Files->extract_zip($temp_file_path, $this->Theamus->file_path(ROOT.'/features/'.$this->feature_config['folder_name']))) {
            $this->clean_temp_folder(); // Clean the temp folder

            // Roll back any changes
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            $this->Theamus->Log->error('Failed to extract feature files to the feature folder.'); // Log the error
            throw new Exception('Failed to install the feature files.');
        }

        $this->clean_temp_folder(); // Clean the temp folder

        // Commit the database changes
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();

        return true; // Return true!
    }


    /**
     * Uploads a feature file that will update the feature as well as saving feature
     * information
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function edit_feature($args) {
        // Check for an administrator with the proper permission
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('edit_features')) {
            die('Only administrators with the proper permissions can do that');
        }

        // Check for an ID and a valid one at that
        if (!isset($args['id']) || $args['id'] == '' || !is_numeric($args['id'])) throw new Exception('Invalid ID.');

        // Check for the feature's existance
        $feature = $this->get_feature($args['id']);

        // Transaction sql!
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);

        // Only if there are files, do this
        if (isset($_FILES['file'])) {
            $file = $_FILES['file']; // Shorter variable names

            // Define the extension and temporary name from the file uploaded
            $name_array = explode('.', $file['name']);
            $extension = $name_array[count($name_array) - 1];
            $temp_name = md5(time());

            // Define the path to the uploaded zip and the extracted folder
            $temp_file_path = $this->Theamus->file_path(ROOT.'/features/features/temp/'.$temp_name.'.'.$extension);
            $temp_folder_path = $this->Theamus->file_path(ROOT.'/features/features/temp/'.$temp_name);

            // Check the file type
            if ($extension !== 'zip') throw new Exception('Invalid file type.');

            // Upload the file to the temp folder
            $this->upload_file($file['tmp_name'], $temp_file_path, $temp_folder_path);

            // Check the configuration stuffs
            $this->get_check_config($temp_folder_path, true);

            // Run the scripts associated to the feature
            $this->run_feature_scripts($temp_folder_path, 'update', $feature['db_prefix']);
        }

        // Check for the 'groups' variable
        if (!isset($args['groups'])) {
            $this->clean_temp_folder(); // Clean the temp folder
            throw new Exception('Invalid groups.');
        }

        // Define the default groups variable if there isn't one set
        if ($args['groups'] == '') $args['groups'] = 'administrators';

        // Check for the 'enabled' variable and redefine it
        if (!isset($args['enabled'])) $args['enabled'] = '0';
        $args['enabled'] = $args['enabled'] === true ? '1' : '0';

        // Query the database, updating the feature information
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('features'),
            array('groups' => $args['groups'],
                'enabled' => $args['enabled']),
            array('operator' => '',
                'conditions' => array('id' => $args['id'])));

        // Check the query for errors
        if (!$query) {
            $this->clean_temp_folder(); // Clean the temp folder

            // Roll back any changes
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            throw new Exception('Failed to update the feature information.');
        }

        // Only install the files if there are files to install
        if (isset($_FILES['file'])) {
            $feature_path = $this->Theamus->file_path(ROOT.'/features/'.$this->feature_config['folder_name']);

            // Make sure the feature folder exists already
            if (!is_dir($feature_path)) {
                $this->clean_temp_folder();
                throw new Exception('Failed to find the feature folder, did the feature alias change?');
            }

            // Make sure the uploaded config file matches the database records
            if ($this->feature_config['folder_name'] != $feature['alias']) {
                $this->clean_temp_folder();
                throw new Exception('The folder names don\'t match up, did the feature alias change?');
            }

            // Check for errors when extracting the files to their final destination
            if (!$this->Theamus->Files->extract_zip($temp_file_path, $feature_path)) {
                $this->clean_temp_folder(); // Clean the temp folder

                // Roll back any changes
                $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

                $this->Theamus->Log->error('Failed to extract feature files to the feature folder.'); // Log the error
                throw new Exception('Failed to install the feature files.');
            }
        }

        $this->clean_temp_folder(); // Clean the temp folder

        // Commit the database changes
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();

        return true; // Return true!
    }


    /**
     * Removes database tables with a certain prefix
     *
     * @param string $prefix
     * @return boolean
     * @throws Exception
     */
    private function remove_feature_tables($prefix = '') {
        // Check for a blank prefix
        if ($prefix == '') return;

        // Generate the queries to run
        $query = $this->Theamus->DB->custom_query('SHOW TABLES');

        // Check the query for errors
        if (!$query) {
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception('Failed to find feature related tables.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) return;

        // Define the tables to remove
        $remove_tables = array();

        $results = $this->Theamus->DB->fetch_rows($query, 'fetch_array', PDO::FETCH_COLUMN);
        $tables = isset($results[0]) ? $results : array($results);

        foreach ($tables as $table) {
            if (strpos($table, $prefix) !== false) {
                $remove_tables[] = sprintf('`%s`', $table);
            }
        }

        // Drop the related tables
        if (!empty($remove_tables)) {
            $query = $this->Theamus->DB->custom_query(sprintf('DROP TABLES %s;', implode(', ', $remove_tables)));

            if (!$query) {
                $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();
                $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
                throw new Exception('Failed to remove feature related tables.');
            }
        }

        return true;
    }


    /**
     * Removes feature files and the database existance
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function remove_feature($args) {
        // Check for an administrator with the proper permission
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('remove_features')) {
            die('Only administrators with the proper permissions can do that');
        }

        // Check for an ID and a valid one at that
        if (!isset($args['id']) || $args['id'] == '' || !is_numeric($args['id'])) throw new Exception('Invalid ID.');

        // Check for the feature's existance
        $feature = $this->get_feature($args['id']);

        // Transaction sql!
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);

        // Query the database, removing the feature from it
        $query = $this->Theamus->DB->delete_table_row(
            $this->Theamus->DB->system_table('features'),
            array('operator' => '',
                'conditions' => array('id' => $args['id'])));

        // Check the query for errors
        if (!$query) {
            // Roll back any changes
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            throw new Exception('Failed to remove the feature.');
        }

        // Look for an alias, just to make sure we don't delete the feature folder
        if (!isset($feature['alias']) || $feature['alias'] == '') throw new Exception('Failed to find the feature folder.');

        // Look for a table prefix to delete related tables with
        if (!isset($feature['db_prefix']) || $feature['db_prefix'] == '') throw new Exception('Failed to find the feature table prefix.');

        // Remove the feature tables
        $this->remove_feature_tables($feature['db_prefix']);

        // Remove the feature files from existance
        if (!$this->Theamus->Files->remove_folder($this->Theamus->file_path(ROOT.'/features/'.$feature['alias']))) {
            // Roll back any changes
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            throw new Exception('Failed to remove the feature files.');
        }

        // Commit the database changes
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();

        return true; // Return true!
    }
    
    
    /**
     * Creates a feature, or better, sets up a developer with a feature and generated
     * config/file information files.
     * 
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function create_feature($args = array()) {
        if ($this->Theamus->settings['developer_mode'] == 0) {
            throw new Exception("Developer mode must be enabled to create features via the web.  If you want to do this without developer mode, you must do it manually.", 60001);
        }
        
        if (!$this->Theamus->User->is_admin() && !$this->Theamus->User->has_permission("create_features")) {
            throw new Exception("You do not have the proper permissions that are required to create features through the web.", 60002);
        }
        
        if (!isset($args['features_create-feature-name']) || $args['features_create-feature-name'] == "") {
            throw new Exception("Failed to create the feature because of an invalid feature name.  It can't be blank or it wasn't found.", 60003);
        }
        
        if (!isset($args['features_create-feature-alias']) || $args['features_create-feature-alias'] == "") {
            throw new Exception("Failed to create the feature because of an invalid alias/folder name.  It can't be blank or it wasn't found.", 60004);
        }
        
        $this->check_writable_features_directory();
        $this->create_feature_folders($args['features_create-feature-alias']);

        try {
            $this->generate_feature_files($args);
            $this->add_created_feature_to_db($args['features_create-feature-alias'], $args['features_create-feature-name']);
        } catch (Exception $ex) {
            $this->cleanup_after_issues($args['features_create-feature-alias']);
            throw new Exception($ex->getMessage());
        }
        
        $this->Theamus->Log->system("{$this->Theamus->User->user['username']} created feature {$args['features_create-feature-name']}.");
        
        return true;
    }
    
    
    /**
     * Checks to see if the features directory is writable so a feature can be generated there
     * 
     * @return string
     * @throws Exception
     */
    private function check_writable_features_directory() {
        $features_directory = $this->Theamus->file_path(ROOT."/features/");
        if (!is_writable($features_directory)) {
            throw new Exception("The features directory is not writable.", 60005);
        } else return $features_directory;
    }
    
    
    /**
     * Creates the bare minimum required for a feature to work in terms of folders
     * 
     * @param string $folder_name
     * @return boolean
     * @throws Exception
     */
    private function create_feature_folders($folder_name = "") {
        if (strpbrk($folder_name, "\\/?%*:|\"<>") !== FALSE) {
            throw new Exception("Failed to create the feature folder because the value provided contains invalid characters.", 60006);
        } else {
            $folder_path = $this->Theamus->file_path(ROOT."/features/{$folder_name}");
            
            if (is_dir($folder_path)) {
                throw new Exception("A feature with that folder name already exists.  Be more unique!", 60014);
            }
            
            if (!mkdir($folder_path)) {
                throw new Exception("Failed to create the feature folder. ¯\_(ツ)_/¯", 60007);
            } else {
                $view_folder_path = $this->Theamus->file_path("{$folder_path}/views");
                if (!mkdir($view_folder_path)) {
                    throw new Exception("Failed to create the views folder for the feature.", 60008);
                } else return true;
            }
        }
    }
    
    
    /**
     * Runs functions (and logs) to generate the bare minimum files required for a feature to work
     * 
     * @param array $args
     */
    private function generate_feature_files($args = array()) {
        $this->generate_config_file($args['features_create-feature-alias'], $args['features_create-feature-name']);
        $this->generate_info_file($args['features_create-feature-alias'], $args['features_create-feature-name']);
        $this->generate_index_file($args['features_create-feature-alias']);
        
        $this->Theamus->Log->system("Generated folder contents for the new feature {$args['features_create-feature-name']}.");
    }
    
    
    /**
     * Generates the config.php file based off of a sample file with the information
     * provided from the form
     * 
     * @param string $folder_name
     * @param string $feature_name
     * @return boolean
     * @throws Exception
     */
    private function generate_config_file($folder_name, $feature_name) {
        $dev_string = strtoupper($folder_name)."_DEV_MODE";
        
        $sample_path = $this->Theamus->file_path(ROOT."/features/features/views/developer/samples/config-sample.php");
        $generated_path = $this->Theamus->file_path(ROOT."/features/{$folder_name}/config.php");
    
        if (!file_exists($sample_path)) {
            throw new Exception("The sample configuration file (used to generate the new feature's config file) doesn't exist or can't be found.", 60009);
        }
        
        $sample_contents = file_get_contents($sample_path);
        
        $new = str_replace("<folder_dev_mode>", $dev_string, 
                str_replace("<folder_name>", $folder_name, 
                str_replace("<feature_name>", $feature_name,
                str_replace("<user_name>", $this->Theamus->User->user['firstname']." ".$this->Theamus->User->user['lastname'],
                str_replace("<user_username>", $this->Theamus->User->user['username'],
                str_replace("<user_email>", $this->Theamus->User->user['email'], $sample_contents))))));
       
        $handle = fopen($generated_path, "x");
        if (!$handle) {
            throw new Exception("Failed to generate the configuration file for the feature.", 60015);
        }
        
        fwrite($handle, $new);
        fclose($handle);
        return true;
    }
    
    
    /**
     * Generates the files.info.php file based off of a sample file with the information
     * provided from the form
     * 
     * @param string $folder_name
     * @param string $feature_name
     * @return boolean
     * @throws Exception
     */
    private function generate_info_file($folder_name, $feature_name) {
        $sample_path = $this->Theamus->file_path(ROOT."/features/features/views/developer/samples/files.info-sample.php");
        $generated_path = $this->Theamus->file_path(ROOT."/features/{$folder_name}/files.info.php");
        
        if (!file_exists($sample_path)) {
            throw new Exception("The sample file information file (used to generate files.info.php) doesn't exist or couldn't be found.", 60010);
        }
        
        $sample_contents = file_get_contents($sample_path);
        
        $new = str_replace("<feature_name>", $feature_name, $sample_contents);
        
        $handle = fopen($generated_path, "x");
        if (!$handle) {
            throw new Exception("Failed to generate the file information file for the feature.", 60011);
        }
        
        fwrite($handle, $new);
        fclose($handle);
        return true;
    }
    
    
    /**
     * Generates a super simple index.php file that just says "Hello, world!"
     * 
     * @param string $folder_name
     * @return boolean
     * @throws Exception
     */
    private function generate_index_file($folder_name) {
        $generated_path = $this->Theamus->file_path(ROOT."/features/{$folder_name}/views/index.php");
        
        $handle = fopen($generated_path, "x");
        if (!$handle) {
            throw new Exception("Failed to generate the first view file (views/index.php) for the feature.", 60012);
        }
        
        fwrite($handle, "Hello, world!");
        fclose($handle);
        return true;
    }
    
    
    /**
     * Adds the newly created feature to the database
     * 
     * @param string $folder_name
     * @param string $feature_name
     * @return boolean
     * @throws Exception
     */
    private function add_created_feature_to_db($folder_name, $feature_name) {
        $prefix = substr(md5(microtime()), 0, 6);
        
        $query = $this->Theamus->DB->insert_table_row(
                $this->Theamus->DB->system_table("features"),
                array("alias"   => $folder_name,
                    "name"      => $feature_name,
                    "groups"    => "administrators",
                    "permanent" => 0,
                    "enabled"   => 1,
                    "db_prefix" => $prefix));
        
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to add the feature information to the database.", 60013);
        }
        
        $this->Theamus->Log->system("Added a newly created feature ({$feature_name}) to the database.");
        
        return true;
    }
    
    
    /**
     * Removes all signs of a newly created feature folder if something goes wrong.
     * 
     * @param string $folder_name
     * @return boolean
     */
    public function cleanup_after_issues($folder_name) {
        $folder_path = $this->Theamus->file_path(ROOT."/features/{$folder_name}");
        
        if (!is_dir($folder_path)) return true;
        
        return $this->Theamus->Files->remove_folder($folder_path);
    }
}