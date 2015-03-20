<?php

class Appearance {
    protected $Theamus;

    /**
     * Connects to Theamus and looks for administrators
     *
     * @param object $t
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;

        if (!$t->User->is_admin()) die('Only administrators have access to the Appearance feature.');

        return;
    }


    /**
     * Cleans the temp folder of all folders/files that aren't "blank.txt"
     *
     * @return
     */
    private function clean_temp_folder() {
        // Define the path to the temp folder
        $path = ROOT."/features/appearance/temp";

        // Scan the folder for files
        $files = $this->Theamus->Files->scan_folder($path);

        // Scan the folder for folders
        $folders = $this->Theamus->Files->scan_folder($path, false, "folders");

        // Remove all files
        foreach ($files as $f) {
            // Check for anything that isn't the filler file and remove it
            if ($f != $this->Theamus->file_path($path."/blank.txt")) unlink($f);
        }

        // Remove all folders
        foreach ($folders as $f) $this->Theamus->Files->remove_folder($f);

        return; // Return!
    }


    /**
     * Define the appearance tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function appearance_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('List of Themes', 'appearance/index.php', 'Theamus Themes'),
            array('Install a Theme', 'appearance/install.php', 'Install a Theme'),
            array('More', array(
                array('Favicon Settings', 'appearance/favicon.php', 'Favicon Settings')
            ), 'right'));

        // Return the HTML tabs
        return $this->Theamus->Theme->generate_admin_tabs("appearance-tab", $tabs, $file);
    }


    /**
     * Gets information about a theme from the database
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get_theme_info($id) {
        // Check for a valid ID
        if ($id == '' || !is_numeric($id)) throw new Exception('Invalid ID.');

        // Query the database for the related theme
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('themes'),
            array(),
            array('operator' => '',
                'conditions' => array('id' => $id)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log query errors
            throw new Exception('Failed to get theme information.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find theme.');

        // Return the theme information
        return $this->Theamus->DB->fetch_rows($query);
    }


    /**
     * Installs a theme in the database and the ROOT/themes folder
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function install_theme($args) {
        // Check for wether or not this is an update of a theme or an installation
        $update = (!isset($args['update']) || !$args['update']) ? false : true;

        // Check permissions for INSTALLING a theme
        if (!$update && (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('install_themes'))) {
            die('Only administrators with proper permissions can Install Themes.');

        // Check permissions for UPDATING a theme
        } elseif ($update && (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('edit_themes'))) {
            die('Only administrators with proper permissions can Edit Themes.');
        }

        // Check for a file to be uploaded
        if (count($_FILES) == 0) throw new Exception('Choose a file to upload.');

        // Check for the proper file to be uploaded
        if (!isset($_FILES['file'])) throw new Exception('Failed to find the uploaded file.');

        $file = $_FILES['file']; // Shorthand variable

        // Define the name/extension and the temp name for the upload zip and folder
        $file_name_array = explode('.', $file['name']);
        $temp_name = md5(time());

        // Define the path for the zip and the folder
        $temp_zip_path    = $this->Theamus->file_path(ROOT.'/features/appearance/temp/'.$temp_name.'.'.$file_name_array[count($file_name_array) - 1]);
        $temp_folder_path = $this->Theamus->file_path(ROOT.'/features/appearance/temp/'.$temp_name);

        // Check for any issues when actually updating the file
        if (!move_uploaded_file($file['tmp_name'], $temp_zip_path)) {
            $this->Theamus->Log->error('Failed to upload Theme file.  Check permissions.'); // Log permissions error
            throw new Exception('Failed to upload the file.');
        }

        // Check for any issues when extracting the upload to the temp folder
        if (!$this->Theamus->Files->extract_zip($temp_zip_path, $temp_folder_path)) {
            $this->clean_temp_folder();
            throw new Exception('Failed to extract the uploaded file.');
        }

        // Define the path to the theme configuration file
        $config_file_path = $this->Theamus->file_path($temp_folder_path.'/config.json');

        // Check for an existing configuration file
        if (!file_exists($config_file_path)) throw new Exception('Failed to find the theme configuration file.');

        // Define the config file information
        $config = json_decode(file_get_contents($config_file_path), true);

        // Check for some required variables in the config file
        if (!isset($config['theme'])) throw new Exception('Failed to get theme configuration information.');
        if (!isset($config['theme']['folder'])) throw new Exception('Failed to find the theme folder name.');
        if (!isset($config['theme']['name'])) throw new Exception('Failed to find the theme name.');

        // Check for an INSTALLATION ONLY
        if (!$update) {
            // Check if the theme has already been installed (or another one with the same name)
            if (is_dir($this->Theamus->file_path(ROOT.'/themes/'.$config['theme']['folder']))) {
                $this->clean_temp_folder();
                throw new Exception('A theme with the same folder name already has been installed.');
            }

            // Query the database, adding this theme information
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);
            $query = $this->Theamus->DB->insert_table_row(
                $this->Theamus->DB->system_table('themes'),
                array('alias'   => $config['theme']['folder'],
                    'name'      => $config['theme']['name'],
                    'active'    => 0,
                    'permanent' => 0));

            // Check the query for errors
            if (!$query) {
                $this->clean_temp_folder();
                $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
                $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();
                throw new Exception('Failed to install the theme.');
            }
        }

        // Define the path to the theme folder (not temp)
        $theme_path = $this->Theamus->file_path(ROOT.'/themes/'.$config['theme']['folder']);

        // Check for issues when extracting the uploaded files to the themes folder
        if (!$this->Theamus->Files->extract_zip($temp_zip_path, $theme_path)) {
            $this->clean_temp_folder();
            if (!$update) $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();
            throw new Exception('Failed to install the theme files.');
        }

        // Commit the database stuffs
        if (!$update) $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();

        $this->clean_temp_folder(); // Clean up the temp folder
        return true; // Return true!
    }


    /**
     * Changes the current theme
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function set_active_theme($args) {
        // Check for administrators
        if (!$this->Theamus->User->is_admin()) {
            die('Only administrators can change the activity of themes.');
        }

        // Check for a valid ID
        if (!isset($args['id']) || $args['id'] == '' || !is_numeric($args['id'])) {
            throw new Exception('Invalid ID.');
        }

        // Query the database, updating with the new information
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('themes'),
                array(array('active' => 0),
                    array('active' => 1)),
                array(array(),
                    array('operator' => '', 'conditions' => array('id' => $args['id']))));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            throw new Exception('Failed to update theme.');
        }

        return true; // Return true!
    }


    /**
     * Removes a theme from the database and the file structure
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function remove_theme($args) {
        // Check for administrators with permission
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('remove_themes')) {
            die('Only administrators with the proper permissions can remove themes.');
        }

        // Check for a valid ID
        if (!isset($args['id']) || $args['id'] == '' || !is_numeric($args['id'])) throw new Exception('Invalid ID.');

        // Get the theme information (check if it exists)
        $theme = $this->get_theme_info($args['id']);

        // Query the database, removing the theme from it
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);
        $query = $this->Theamus->DB->delete_table_row(
            $this->Theamus->DB->system_table('themes'),
            array('operator' => '',
                'conditions' => array('id' => $args['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query errors
            throw new Exception('Failed to remove theme.');
        }

        // Define the path to the theme folder
        $theme_path = $this->Theamus->file_path(ROOT.'/themes/'.$theme['alias']);

        // Check for issues when removing the theme folder
        if (!$this->Theamus->Files->remove_folder($theme_path)) {
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();
            throw new Exception('Failed to remove theme files');
        }

        // Commit the removal from the database
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();

        $this->clean_temp_folder(); // Clean the temp folder, just cuz

        return true; // Return true!
    }
    
    public function update_favicon($args = array()) {
        if (!$this->Theamus->User->is_admin()) {
            throw new Exception("Only administrators can change the favicon.");
        }
        
        if (!isset($args['appearance_favicon-path'])) $args['appearance_favicon-path'] = "";
        
        $query = $this->Theamus->DB->update_table_row(
                $this->Theamus->DB->system_table("settings"),
                array("favicon_path" => $args['appearance_favicon-path']));
        
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
            throw new Exception("Failed to save the favicon path because of a query error.");
        }
        
        $this->Theamus->Log->system("Updated site favicon.");
        return true;
    }
}