<?php

class DefaultAdmin {
    protected $Theamus;
    protected $db_apps = array();
    protected $home_apps_folder = '';
    protected $installed_apps = 0;
    protected $deleted_apps = 0;


    /**
     * Connects to Theamus
     *
     * @param object $t
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;

        // Administrators only can be here
        if (!$this->Theamus->User->is_admin()) throw new Exception('Only administrators have access to this.');

        $this->home_apps_folder = $this->Theamus->file_path(ROOT.'/features/default/home-apps');
        return;
    }


    /**
     * Finds all of the folders in the home-apps folder
     *
     * @return array
     */
    protected function get_app_folders() {
        // Return any folders found in the home apps folder
        return $this->Theamus->Files->scan_folder($this->home_apps_folder, $this->home_apps_folder, 'folders');
    }


    /**
     * Gets all of the home apps from the database
     *
     * @return array
     * @throws Exception
     */
    protected function get_db_apps() {
        // Query the database for all of the apps
        $query = $this->Theamus->DB->select_from_table(
            'dflt_home-apps',
            array('path'));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query('Query Error: '.$this->Theamus->DB->get_last_error()); // Log the error in the database
            throw new Exception('Failed to find all of the home apps.'); // Throw an exception to the user
        }

        // Define the apps
        $results = $this->Theamus->DB->fetch_rows($query);
        return isset($results[0]) ? $results : array($results);
    }


    /**
     * Finds the difference of apps in the database and the folder.
     *
     * @return array
     * @throws Exception
     */
    protected function check_folder_existance() {
        // Define the apps in the home-apps folder
        $app_folders = $this->get_app_folders();

        $db_apps = array(); // Initialize the db apps array

        // Define the app paths from the database in a flatter array
        foreach ($this->db_apps as $app) $db_apps[] = $app['path'];

        $return = array(); // Initalize apps return variable
        foreach ($app_folders as $folder) {
            // Check the app folder to be in the database
            if (!in_array($folder, $db_apps)) $return[] = $folder;
        }

        return $return; // Return the difference
    }


    /**
     * Check for the folder in the home-app to be in the database
     *
     * @return array
     */
    protected function check_db_existance() {
        // Define the apps in the home-apps folder
        $app_folders = $this->get_app_folders();

        $return = array(); // Initialize the return variable

        // Loop through all of the apps found in the database
        foreach ($this->db_apps as $app) {
            // Check the database apps against the folder apps
            if (!in_array($app['path'], $app_folders)) $return[] = $app['path'];
        }

        return $return; // Return the difference
    }


    /**
     * Installs new home apps in the database
     *
     * @return boolean
     * @throws Exception
     */
    protected function install_new_apps() {
        // Define the apps that need to be installed
        $new_apps = $this->check_folder_existance();

        $query_data = array(); // Initialize the query data array

        // Loop through all of the new apps
        foreach ($new_apps as $app) {
            // Check for a configuration file in the home app folder
            if (!file_exists($this->Theamus->file_path($this->home_apps_folder.'/'.$app.'/config.php'))) {
                throw new Exception('Failed to install "'.$app.'".  No configuration file was found.');
            }

            $homeapp = array(); // Initialize the homeapp config info array

            // Include the configuration file and check the required variables
            include $this->Theamus->file_path($this->home_apps_folder.'/'.$app.'/config.php');
            if (!isset($homeapp['title'])) throw new Exception('Failed to install "'.$app.'".  The configuration file is missing the variable "title".');
            if (!isset($homeapp['alias'])) throw new Exception('Failed to install "'.$app.'".  The configuration file is missing the variable "alias".');

            // Add this app information to the query data
            $query_data[] = array(
                'name'      => $homeapp['title'],
                'path'      => $homeapp['alias'],
                'active'    => 1,
                'position'  => 1,
                'column'    => 1);
        }

        $this->installed_apps = count($query_data); // Define the amount of apps to be installed

        // Check for any apps to be installed
        if ($this->installed_apps > 0) {
            // Add the query data to the database table
            $query = $this->Theamus->DB->insert_table_row(
                'dflt_home-apps',
                $query_data);

            // Check the query for errors
            if (!$query) {
                $this->Theamus->Log->query('Query Error: '.$this->Theamus->DB->get_last_error()); // Log the error
                throw new Exception('Failed to install new home apps.');
            }
        }

        return true; // Return true!
    }


    /**
     * Deletes apps from the database table that weren't found in the home apps folder
     *
     * @return boolean
     * @throws Exception
     */
    protected function delete_missing_apps() {
        // Define the missing apps that need to be deleted from the database
        $missing_apps = $this->check_db_existance();

        $query_data = array(); // Initialize the query data array

        // Loop through all of the missing apps
        foreach($missing_apps as $app) {
            // Add the missing app information to the query data
            $query_data[] = array(
                'operator'   => '',
                'conditions' => array('path' => $app));
        }

        $this->deleted_apps = count($query_data); // Define the amount of apps to be deleted

        // Check for any apps to be deleted
        if ($this->deleted_apps > 0) {
            // Query the database, removing the apps
            $query = $this->Theamus->DB->delete_table_row(
                'dflt_home-apps',
                $query_data);

            // Check the query for errors
            if (!$query) {
                $this->Theamus->Log->query('Query Error: '.$this->Theamus->DB->get_last_error()); // Log the error
                throw new Exception('Failed to delete home apps.');
            }
        }

        return true; // Return true!
    }


    /**
     * Updates the apps found in the home apps folder to coincide with the database
     *
     * @return string|boolean
     * @throws Exception
     */
    public function update_apps() {
        // Check for an administrator
        if (!$this->Theamus->User->is_admin()) throw new Exception('Only administrators can do this.');

        // Define the apps in the database to cut down query calls
        $this->db_apps = $this->get_db_apps();

        $this->install_new_apps(); // Install any new apps
        $this->delete_missing_apps(); // Delete any missing apps

        // Run the installer/deleter and respond accordingly
        if ($this->installed_apps > 0 || $this->deleted_apps > 0) {
            $installed = $this->installed_apps == 1 ? '1 installed app' : $this->installed_apps.' installed apps';
            $deleted = $this->deleted_apps == 1 ? '1 deleted app' : $this->deleted_apps.' deleted apps' ;
            return 'Changes were made to the admin home apps - '.$installed.' and '.$deleted.'.';
        }

        return true; // Return true!
    }


    /**
     * Saves the home apps positions in the database
     *
     * @param array $args
     * @return string|boolean
     * @throws Exception
     */
    public function save_app_positions($args = array()) {
        // Check for an administrator
        if (!$this->Theamus->User->is_admin()) throw new Exception('Only administrators can do this.');

        $query_data = array(); // Initialize the query data information

        // Loop through the arguments to define the information for the database table
        foreach ($args as $key => $value) {
            // Define all of the apps from the strings given
            $apps = strpos($value, ',') !== false ? explode(',', $value) : array($value);

            // Loop through all of the apps
            foreach ($apps as $app) {
                // Explode the app information into an array to get the path and new position
                $app_info = explode('=', $app);

                $query_data['data'][] = array(
                    'position'  => $app_info[1],
                    'column'    => str_replace('column', '', $key));
                $query_data['clause'][] = array(
                    'operator'   => '',
                    'conditions' => array('path' => $app_info[0]));
            }
        }

        // Check for any query data
        if (!empty($query_data)) {
            // Query the database with the information to save the app positions
            $query = $this->Theamus->DB->update_table_row(
                'dflt_home-apps',
                $query_data['data'],
                $query_data['clause']);

            // Check the query for errors
            if (!$query) {
                $this->Theamus->Log->query('Query Error: '.$this->Theamus->DB->get_last_error()); // Log the query error in the database
                throw new Exception('Failed to save app positions.'); // Throw an exception to the user
            }

            return 'Home app positions have been saved.';
        }

        return true;
    }


    /**
     * Saves the activity of the home apps to the database
     *
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function save_home_apps($args = array()) {
        // Check for an administrator
        if (!$this->Theamus->User->is_admin()) throw new Exception('Only administrators can do this.');

        // Check the apps argument and error out to the user
        if (!isset($args['apps']) || $args['apps'] == '') throw new Exception('No apps were set to update.');

        $query_data = array(); // Initialize the query data variable

        // Define the apps into an array of their own
        $apps = strpos($args['apps'], ',') !== false ? explode(',', $args['apps']) : array($args['apps']);

        // Loop through the apps
        foreach ($apps as $app) {
            // Define their own information
            $app = explode('=', $app);

            // Add this app info to the query data
            $query_data['data'][] = array('active' => $app[1]);
            $query_data['clause'][] = array(
                'operator'   => '',
                'conditions' => array('path' => $app[0]));
        }

        // Query the database, updating it with the information gathered
        $query = $this->Theamus->DB->update_table_row(
            'dflt_home-apps',
            $query_data['data'],
            $query_data['clause']);

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query('Query Error: '.$this->Theamus->DB->get_last_error()); // Log the query error
            throw new Exception('Failed to save this information.'); // Throw an error to the user
        }

        return 'Saved.'; // Return saved
    }


    /**
     * Define the admin tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function admin_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('View Dashboard', 'admin-index.php', 'Theamus Dashboard'),
            array('Manage Dashboard Apps', 'admin/manage-apps.php', 'Manage Dashboard Apps')
        );

        $return_tabs = array(); // Empty return array to add to

        // Loop through all of the tabs defined above and assign them to li items/links
        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : ''; // Define the current tab
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'admin-tab\' data-file=\'/default/'.trim($tab[1], '.php').'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        // Return the tabs to the page
        return '<ul>'.implode('', $return_tabs).'</ul>';
    }
}