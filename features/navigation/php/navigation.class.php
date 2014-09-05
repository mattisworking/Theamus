<?php

class Navigation {
    protected $Theamus;


    /**
     * Connects to theamus
     *
     * @param object $t
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;
        return;
    }

    /**
     * Define the navigation tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function navigation_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('List of Links', 'navigation/index.php', 'Theamus Navigation'),
            array('Search Links', 'navigation/search.php', 'Theamus Navigation'),
            array('Create a New Link', 'navigation/create.php', 'Create a New Link'));

        // Return the HTML tabs
        return $this->Theamus->Theme->generate_admin_tabs("navigation-tab", $tabs, $file);
    }


    /**
     * Gets the currently active theme folder from the database
     *
     * @return array
     * @throws Exception
     */
    private function get_current_theme_folder() {
        // Query the database for the currently active theme
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('themes'),
                array(),
                array('operator' => '',
                    'conditions' => array('active' => 1)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to get the currently active theme.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find the currently active theme.');

        // Define the theme information from the database
        $theme = $this->Theamus->DB->fetch_rows($query);

        // Return the theme folder
        return isset($theme['alias']) ? $theme['alias'] : 'default';
    }


    /**
     * Gets the navigation options from the currently active theme
     *
     * @return array
     * @throws Exception
     */
    private function get_link_positions() {
        // Define the path to the theme config file
        $path = $this->Theamus->file_path(ROOT.'/themes/'.$this->get_current_theme_folder().'/config.json');

        // Check if the file doesnt exist
        if (!file_exists($path)) throw new Exception('Failed to find the theme config file.');

        // Define the configuration information into an array
        $config = json_decode(file_get_contents($path));

        // Return the navigation options, if there are any
        return isset($config->navigation) ? $config->navigation : array();
    }


    /**
     * Takes all of the possible navigation options for a theme and returns
     * them as options for a select element
     *
     * @param string $current
     * @return string
     */
    public function get_positions_select($current = '') {
        // Use try/catch to gracefully handle any errors that might occur
        try {
            // Define the possible link positions from the current theme
            $positions = $this->get_link_positions();

            // Check for no positions
            if (empty($positions)) return '<option value="main">Main</option>';

            $return = array(); // Initialize the return array

            // Loop through all of the options adding them to a return array
            foreach ($positions as $spot) {
                $selected = $current == $spot ? 'selected' : '';
                $return[] = '<option value="'.$spot.'" '.$selected.'>'.ucwords(str_replace('_', ' ', $spot)).'</option>';
            }

            // Return the array of options as a string
            return implode('', $return);

        // Return the error as a blank option
        } catch (Exception $ex) { return '<option>'.$ex->getMessage().'</option>'; }
    }


    /**
     * Get links from the database to choose a parent link
     *
     * @param int $child_of
     * @return string
     */
    public function get_children_select($child_of = 0) {
        // Initialize the return array
        $return = array('<option value="0">Not a Child</option>');

        // Query the database for all links
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('links'),
                array('id', 'text'),
                array(),
                'ORDER BY `text` ASC');

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Return the error message as an option
            return '<option>Failed to get Theamus Links</option>';
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) > 0) {
            // Define the query information
            $results = $this->Theamus->DB->fetch_rows($query);

            // Loop through the results
            foreach (isset($results[0]) ? $results : array($results) as $link) {
                $selected = $link['id'] == $child_of ? 'selected' : '';
                $return[] = '<option value="'.$link['id'].'" '.$selected.'>'.$link['text'].'</option>';
            }
        }

        // Return all of the options as a string
        return implode('', $return);
    }


    /**
     * Gets Theamus Pages from the database and returns them as options for a
     * select element
     *
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function get_pages_select($args) {
        // Define the page alias
        $page_alias = isset($args['page']) ? $args['page'] : '';

        // Query the database for all of the site's pages
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('pages'),
                array('alias', 'title'));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to get Theamus Pages.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find any Theamus Pages.');

        // Define the query information
        $results = $this->Theamus->DB->fetch_rows($query);

        $return = array(); // Initialize the return array

        // Loop through the query results
        foreach (isset($results[0]) ? $results : array($results) as $page) {
            $selected = $page['alias'] == $page_alias ? 'selected' : '';
            $return[] = '<option value="'.$page['alias'].'" '.$selected.'>'.$page['title'].'</option>';
        }

        // Return the array of options as a string
        return implode('', $return);
    }


    /**
     * Gets features from the database and returns them as options for a select element
     *
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function get_features_select($args) {
        // Define the page alias
        $feature_alias = isset($args['feature']) ? $args['feature'] : '';

        // Query the database for all of the site's pages
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('features'),
                array('alias', 'name'),
                array(),
                'ORDER BY `name` ASC');

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to get Theamus Features.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find any Theamus Features.');

        // Define the query information
        $results = $this->Theamus->DB->fetch_rows($query);

        $return = array(); // Initialize the return array

        // Loop through the query results
        foreach (isset($results[0]) ? $results : array($results) as $feature) {
            $selected = $feature['alias'] == $feature_alias ? 'selected' : '';
            $return[] = '<option value="'.$feature['alias'].'" '.$selected.'>'.$feature['name'].'</option>';
        }

        // Return the array of options as a string
        return implode('', $return);
    }


    /**
     * Gets features from the database and returns them as options for a select element
     *
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function get_groups_select($args) {
        // Define the page alias
        $groups = isset($args['groups']) ? explode(',', $args['groups']) : array();

        // Query the database for all of the site's pages
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('groups'),
                array('alias', 'name'),
                array(),
                'ORDER BY `name` ASC');

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to get Theamus Groups.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find any Theamus Groups.');

        // Define the query information
        $results = $this->Theamus->DB->fetch_rows($query);

        $return = array(); // Initialize the return array

        // Loop through the query results
        foreach (isset($results[0]) ? $results : array($results) as $group) {
            $selected = in_array($group['alias'], $groups) ? 'selected' : '';
            $return[] = '<option value="'.$group['alias'].'" '.$selected.'>'.$group['name'].'</option>';
        }

        // Return the array of options as a string
        return implode('', $return);
    }


    /**
     * Gets the view files for a feature and returns them as options for a select element
     *
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function get_feature_files_select($args) {
        // Check for a feature folder
        if (!isset($args['feature']) || $args['feature'] == '') throw new Exception('Failed to find the Feature Folder.');

        // Define the file name
        $feature_file = isset($args['file']) ? $args['file'] : '';

        // Define the path to the feature's view files
        $path = $this->Theamus->file_path(ROOT.'/features/'.$args['feature'].'/views');

        // Get all of the view files
        $files = $this->Theamus->Files->scan_folder($path, $path);

        // Check for no files
        if (count($files) == 0) throw new Exception('Failed to find feature files.');

        $return = array(); // Initialize the return array

        // Loop through all of the files
        foreach ($files as $file) {
            // Clean up the file name
            $clean_name = ucwords(
                    str_replace('.php', '',
                    str_replace('/', ' / ',
                    str_replace('\\', ' / ',
                    str_replace('_', ' ',
                    str_replace('-', ' ', $file))))));

            // Check if this file is the home_override file
            $selected = $feature_file.'.php' == $file ? 'selected' : '';

            // Show the feature file as an option
            $return[] = '<option value="'.str_replace('.php', '', $file).'" '.$selected.'>'.$clean_name.'</option>';
        }

        return implode('', $return); // Return the feature file options
    }


    /**
     * Gets information about a link
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get_link($id = 0) {
        // Check the id
        if ($id == 0 || !is_numeric($id)) throw new Exception('Invalid ID.');

        // Query the database for the link
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('links'),
                array(),
                array('operator' => '',
                    'conditions' => array('id' => $id)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to get link.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find link.');

        // Return the link information
        return $this->Theamus->DB->fetch_rows($query);
    }


    /**
     * Checks incoming information for values and existance + making things simpler
     *
     * @param array $args
     * @return array
     * @throws Exception
     */
    protected function check_arguments($args, $edit = false) {
        if ($edit) {
            if (!isset($args['id']) || $args['id'] == '' || !is_numeric($args['id'])) throw new Exception('Invalid ID.');
        }

        // Check the link text
        if (!isset($args['text']) || $args['text'] == '') throw new Exception('Invalid link text.');

        // Define the link alias
        $args['alias'] = strtolower(preg_replace('/[^A-Za-z0-9-]/', '', str_replace(' ', '-', $args['text'])));

        // Check the page title and alias length
        if (strlen($args['text']) > 75 || strlen($args['alias']) > 100) throw new Exception('Link text is too long.');

        // Check the link path
        if (!isset($args['path-type']) || $args['path-type'] == '') throw new Exception('Invalid link path.');

        // Check the theme position
        if (!isset($args['position']) || $args['position'] == '') throw new Exception('Invalid position.');

        // Check the child of
        if (!isset($args['child_of']) || $args['child_of'] == '' || !is_numeric($args['child_of'])) throw new Exception('Invalid child of value.');

        // Check the weight
        if (!isset($args['weight']) || $args['weight'] == '' || !is_numeric($args['weight'])) throw new Exception('Invalid weight value.');

        // Check the groups
        if (!isset($args['groups'])) throw new Exception('Failed to find groups.');

        // Define groups default
        $args['groups'] = $args['groups'] == '' ? 'everyone' : $args['groups'];

        // Define the link path type
        $args['path-type'] = str_replace('path-', '', $args['path-type']);

        // Define the path of the link
        switch ($args['path-type']) {
            case 'null':    $args['path'] = $args['null'];      break;
            case 'url':     $args['path'] = $args['url-path'];  break;
            case 'page':    $args['path'] = $args['page'];      break;
            case 'feature': $args['path'] = $args['feature'].'/'.$args['file']; break;
            case 'js':      $args['path'] = $args['js'];        break;
            default:        $args['path'] = $args['url-path'];
        }

        // Check for a path
        if ($args['path'] == '') throw new Exception('Link needs a path.');

        // Clean the path variable
        $args['path'] = $args['path-type'] == 'null' ? 'javascript:void(0);' : trim($args['path'], '/');

        // Define the path for javascript, so users don't have to add mumbo jumbo to it
        if ($args['path-type'] == 'js') {
            $args['path'] = 'javascript:'.(substr($args['path'], -1, 1) == ';' ? $args['path'] : $args['path'].';');
        }

        return $args; // Return the arguments
    }


    /**
     * Creates a new link
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function create_link($args) {
        // Look for an administrator that can create links
        if (!$this->Theamus->User->is_admin() && !$this->Theamus->User->has_permission('create_links')) {
            die('Only administrators with the right permissions can create new links.');
        }

        // Check and define the arguments
        $args = $this->check_arguments($args);

        // Query the database, adding all of the information
        $query = $this->Theamus->DB->insert_table_row(
                $this->Theamus->DB->system_table('links'),
                array('alias'   => $args['alias'],
                    'text'      => $args['text'],
                    'path'      => $args['path'],
                    'weight'    => $args['weight'],
                    'groups'    => $args['groups'],
                    'type'      => $args['path-type'],
                    'location'  => $args['position'],
                    'child_of'  => $args['child_of']));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the quuery error

            throw new Exception('Failed to create link.');
        }

        return true; // Return true!
    }


    /**
     * Updates link information
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function save_link($args) {
        // Look for an administrator that can create links
        if (!$this->Theamus->User->is_admin() && !$this->Theamus->User->has_permission('edit_links')) {
            die('Only administrators with the right permissions can edit links.');
        }

        // Check and define the arguments
        $args = $this->check_arguments($args, true);

        // Check to make sure the link exists (this throws it's own errors)
        $this->get_link($args['id']);

        // Query the database, updating the link
        $query = $this->Theamus->DB->update_table_row(
                $this->Theamus->DB->system_table('links'),
                array('alias'   => $args['alias'],
                    'text'      => $args['text'],
                    'path'      => $args['path'],
                    'weight'    => $args['weight'],
                    'groups'    => $args['groups'],
                    'type'      => $args['path-type'],
                    'location'  => $args['position'],
                    'child_of'  => $args['child_of']),
                array('operator' => '',
                    'conditions' => array('id' => $args['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the quuery error

            throw new Exception('Failed to update the link.');
        }

        return true; // Return true!
    }


    /**
     * Deletes a link
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function remove_link($args) {
        // Look for an administrator that can create links
        if (!$this->Theamus->User->is_admin() && !$this->Theamus->User->has_permission('remove_links')) {
            die('Only administrators with the right permissions can remove links.');
        }

        // Check for a valid ID
        if (!isset($args['id']) || $args['id'] == '' || !is_numeric($args['id'])) throw new Exception('Invalid ID.');

        // Check to make sure the link exists (this throws it's own errors)
        $this->get_link($args['id']);

        // Query the database, updating the link
        $query = $this->Theamus->DB->delete_table_row(
                $this->Theamus->DB->system_table('links'),
                array('operator' => '',
                    'conditions' => array('id' => $args['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the quuery error

            throw new Exception('Failed to remove the link.');
        }

        return true; // Return true!
    }
}