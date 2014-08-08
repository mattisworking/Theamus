<?php

class Groups {
    protected $Theamus;
    public $first_feature = '';

    /**
     * Connect to Theamus
     *
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;
        
        // Check for administrators only
        if (!$this->Theamus->User->is_admin()) throw new Exception('You must be an administrator.');
        
        return;
    }
    

    /**
     * Define the groups tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function groups_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('List of Groups', 'index.php', 'Theamus Groups'),
            array('Search Groups', 'search.php', 'Search Theamus Groups'),
            array('Create a New Group', 'create.php', 'Create a New Group')
        );

        $return_tabs = array(); // Empty return array to add to

        // Loop through all of the tabs defined above and assign them to li items/links
        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : ''; // Define the current tab
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'groups-tab\' data-file=\'groups/'.str_replace('.php', '', $tab[1]).'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        // Return the tabs to the page
        return '<ul>'.implode('', $return_tabs).'</ul>';
    }
    
    
    /**
     * Creates a new Theamus user group
     * 
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function create_group($args) {
        // Check for an user with the ability to create groups
        if (!$this->Theamus->User->has_permission('create_groups')) {
            throw new Exception('Only administrators or people with the right permissions can create groups');
        }
        
        // Check for a valid group name
        if (!isset($args['name']) || $args['name'] == '') throw new Exception('Please fill out the "Group Name" field.');
        
        // Define the alias for the group
        $args['alias'] = strtolower(preg_replace("/[^A-Za-z0-9_]/", '', str_replace(' ', '_', $args['name'])));
        
        // Check the group name and alias for length
        if (strlen($args['name']) > 75 || strlen($args['alias']) > 100) throw new Exception('Group name is too long.');
        
        // Check the user's permissions based on the ones selected
        foreach (explode(',', $args['permissions']) as $permission) {
            if (!$this->Theamus->User->has_permission($permission)) throw new Exception('Cannot create groups with permissions you do not already have.');
        }
        
        // Add the information to the database
        $query = $this->Theamus->DB->insert_table_row(
                $this->Theamus->DB->system_table('groups'),
                array(
                    'alias' => $args['alias'],
                    'name' => $args['name'],
                    'permissions' => $args['permissions'],
                    'permanent' => 0,
                    'home_override' => 'false'));
        
        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            
            throw new Exception('Failed to create group.');
        }
        
        return true; // Return true!
    }
    
    
    /**
     * Gets all of the Theamus permissions from the database and returns them as
     * options for a select element
     * 
     * @param array $group_permissions
     * @return string $return
     */
    public function get_permission_options($group_permissions) {
        $return = array(); // Initialize the return array
        
        // Query the database for permissions
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('permissions'),
                array('permission', 'feature'));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            $return[] = '<option>Failed to get permissions.</option>';
        } else {
            if ($this->Theamus->DB->count_rows($query) == 0) echo '<option>No permissions found.</option>';
            else {
                // Define the permission results
                $results = $this->Theamus->DB->fetch_rows($query);

                // Loop through all of the permissions
                foreach (isset($results[0]) ? $results : array($results) as $permission) {
                    // Clean up the text for the permission and the feature
                    $permission_name    = ucwords(str_replace('_', ' ', $permission['permission']));
                    $permission_feature = ucwords(str_replace('_', ' ', $permission['feature']));

                    // Define the checked status
                    $checked = in_array($permission['permission'], $group_permissions) ? 'selected' : '';

                    // Show the permission as an option
                    $return[] = '<option value="'.$permission['permission'].'" '.$checked.'>'.$permission_feature.' - '.$permission_name.'</option>';
                }
            }
        }
        
        return implode('', $return); // Return the permission options
    }
    
    
    /**
     * Gets all of the Theamus pages from the database and returns them as
     * options for a select element
     * 
     * @param array $home
     * @return string $return
     */
    public function get_page_options($home) {
        // Define the page ID from the home_override information
        $page_id = $home['type'] == 'page' ? $home['id'] : '';
        
        $return = array(); // Initialize the return array

        // Query the database for pages
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('pages'),
                array('id', 'title'));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            echo '<option>Failed to get pages.</option>';
        } else {
            if ($this->Theamus->DB->count_rows($query) == 0) echo '<option>No pages found.</option>';
            else {
                // Define the page results
                $results = $this->Theamus->DB->fetch_rows($query);

                foreach (isset($results[0]) ? $results : array($results) as $page) {
                    // Define the selected status
                    $selected = $page['id'] == $page_id ? 'selected' : '';

                    // Show the page as an option
                    $return[] = '<option value="'.$page['id'].'" '.$selected.'>'.$page['title'].'</option>';
                }
            }
        }
        
        return implode('', $return); // Return the options!
    }
    
    
    /**
     * Gets all of the features from the database and return them as options
     * for a select element
     * 
     * @param array $home
     * @return string $return
     */
    public function get_feature_options($home) {
        // Define the feature ID based on the home_override information
        $feature_id = $home['type'] == 'feature' ? $home['id'] : '';
        
        $return = array(); // Initialize the return array

        // Query the database for features
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('features'),
                array('id', 'alias', 'name'),
                array(),
                'ORDER BY `name` ASC');

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            $return[] = '<option>Failed to get pages.</option>';
        } else {
            if ($this->Theamus->DB->count_rows($query) == 0) echo '<option>No features found.</option>';
            else {
                // Define the feature results
                $results = $this->Theamus->DB->fetch_rows($query);

                // Define the 'first feature' to load files for
                $this->first_feature = $results[0]['alias'];

                foreach (isset($results[0]) ? $results : array($results) as $feature) {
                    // Define the selected status
                    $selected = $feature['id'] == $feature_id ? 'selected' : '';

                    // Show the feature as an option
                    $return[] = '<option value="'.$feature['id'].'" data-alias="'.$feature['alias'].'" '.$selected.'>'.$feature['name'].'</option>';
                }
            }
        }
        
        return implode('', $return); // Return the feature options
    }
    
    
    /**
     * Gets the view files from a feature folder and returns them as options
     * for a select element
     * 
     * @param array $home
     * @return string $return
     */
    public function get_feature_file_options($home) {
        $return = array(); // Initialize the return array
        
        // Define the feature folder
        $feature_folder = !isset($home['feature_folder']) || $home['feature_folder'] == '' ? $this->first_feature : $home['feature_folder'];
        
        if ($feature_folder == '') $return[] = '<option>Failed to find the feature folder</option>';
        
        // Define the path to the feature that's looking for files
        $feature_path = $this->Theamus->file_path(ROOT.'/features/'.$feature_folder.'/views');
        
        //throw new Exception($feature_path);

        // Get all of the view files from the feature
        $files = $this->Theamus->Files->scan_folder($feature_path, $feature_path);

        // Check for no view files
        if (count($files) == 0) $return[] = '<option>There are no view files for this feature.</option>';

        // Loop through all of the files
        foreach ($files as $file) {
            // Clean up the file name
            $clean_name = ucwords(
                    str_replace('.php', '', 
                    str_replace('/', ' / ', 
                    str_replace('\\', ' / ', 
                    str_replace('_', ' ', 
                    str_replace('-', ' ', $file))))));

            $selected = ''; // Initialize the option selected variable

            // Check if this file is the home_override file
            if (isset($home['file']) && $home['file'] != '') $selected = $home['file'].'.php' == $file ? 'selected' : '';

            // Check if the file is the index since the home_override file wasn't defined
            elseif ($file == 'index.php') $selected = 'selected';

            // Show the feature file as an option
            $return[] = '<option value="'.str_replace('.php', '', $file).'" '.$selected.'>'.$clean_name.'</option>';
        }
    
        return implode('', $return); // Return the feature file options
    }
    
    
    /**
     * Gets group information from the database
     * 
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get_group($id) {
        // Check for an ID
        if (!isset($id) || $id == '' || !is_numeric($id)) throw new Exception('Invalid Group ID.');
        
        // Query the database for a group with this id
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('groups'),
                array(),
                array('operator' => '',
                    'conditions' => array('id' => $id)));
        
        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            
            throw new Exception('Failed to find the group.');
        }
        
        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Cannot find group.');
        
        // Return the group information
        return $this->Theamus->DB->fetch_rows($query);
    }
    
    
    /**
     * Saves group information in the database
     * 
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function save_group($args) {
        // Check for an administrator that can edit groups
        if (!$this->Theamus->User->is_admin() && !$this->Theamus->User->has_permission('edit_groups')) {
            die('Only administrators or people with the right positions can edit groups.');
        }
        
        // Check for an ID
        if (!isset($args['id'])) throw new Exception('Failed to find the group ID.');
        
        // Make sure the group exists (will throw exceptions itself)
        $this->get_group($args['id']);
        
        // Check for a homepage
        if (!isset($args['homepage'])) throw new Exception('Failed to find the homepage.');
        
        // Check for permissions
        if (!isset($args['permissions'])) throw new Exception('Failed to find the permissions.');
        
        // Redefine the homepage if it is empty, for whatever reason
        if ($args['homepage'] == '') $args['homepage'] = 'false';
        
        // Make the query to save this information
        $query = $this->Theamus->DB->update_table_row(
                $this->Theamus->DB->system_table('groups'),
                array('permissions' => $args['permissions'],
                    'home_override' => $args['homepage']),
                array('operator' => '',
                    'conditions' => array('id' => $args['id'])));
        
        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            
            throw new Exception('Failed to save the group information.');
        }
        
        return true; // Return true!
    }
    
    
    /**
     * Deletes a group from the database
     * 
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function remove_group($args) {
        // Check for an administrator that can edit groups
        if (!$this->Theamus->User->is_admin() && !$this->Theamus->User->has_permission('remove_groups')) {
            die('Only administrators or people with the right positions can edit groups.');
        }
        
        // Check for an ID
        if (!isset($args['id'])) throw new Exception('Failed to find the group ID.');
        
        // Make sure the group exists (will throw exceptions itself)
        $this->get_group($args['id']);
        
        // Make the query to delete the group
        $query = $this->Theamus->DB->delete_table_row(
                $this->Theamus->DB->system_table('groups'),
                array('operator' => '',
                    'conditions' => array('id' => $args['id'])));
        
        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            
            throw new Exception('Failed to remove the group.');
        }
        
        return true; // Return true!
    }
}