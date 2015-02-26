<?php

class Pages {
    protected $Theamus;

    /**
     * Connect to Theamus
     *
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;
        return;
    }

    /**
     * Define the pages tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function pages_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('List of Pages', 'pages/index.php', 'Theamus Pages'),
            array('Search Pages', 'pages/search.php', 'Search Pages'),
            array('Create a New Page', 'pages/create.php', 'Create a New Page'));

        // Return the HTML tabs
        return $this->Theamus->Theme->generate_admin_tabs("pages-tab", $tabs, $file);
    }


    /**
     * Gets the currently active theme from the database
     *
     * @return string
     * @throws Exception
     */
    private function get_current_theme() {
        // Query the database for the active theme
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('themes'),
            array('alias'),
            array('operator' => '',
                'conditions' => array('active' => '1')));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Notify the user
            throw new Exception('Error finding the active theme.');
        }

        // Define the results from the query
        $results = $this->Theamus->DB->fetch_rows($query);

        return $results['alias']; // Return the folder name of the active theme
    }


    /**
     * Gets all of the theme layout options
     *
     * @return array
     * @throws Exception
     */
    private function get_theme_options() {
        // Define the current theme being used by the side
        $alias = $this->get_current_theme();

        // Define the path to the theme configuration fiel
        $config_path = $this->Theamus->file_path(ROOT.'/themes/'.$alias.'/config.json');

        // Check if the file doesn't exist
        if (!file_exists($config_path)) throw new Exception('Error locating the theme configuration file.');

        $return = array(); // Initialize the return variable

        // Define the layouts from the configuration file and select the layouts only
        $layouts = json_decode(file_get_contents($config_path))->layouts;

        // Loop through the layouts and define their name and navigation possibility
        foreach ($layouts as $layout) {
            $return[$layout->layout]['layout'] = $layout->layout;
            $return[$layout->layout]['nav']    = $layout->allow_nav == true ? 'true' : 'false';
        }

        return $return; // Return the theme layout information
    }


    /**
     * Defines the select element with options
     *
     * @param string $current
     * @return string
     * @throws Exception
     */
    private function set_selectable_layouts($current) {
        // Get the layouts related to the active theme
        $layouts = $this->get_theme_options();

        // Check for layouts
        if (empty($layouts)) throw new Exception('There are no layouts for the currently active theme, the default has been selected.');

        // Initialize the select return array
        $return = array('<select class="form-control" name="layout">');

        // Loop through the layouts and add the options
        foreach($layouts as $layout) {
            $select = $current == $layout['layout'] ? 'selected' : '';
            $return[] = '<option value="'.$layout['layout'].'" '.$select.' data-nav="'.$layout['nav'].'">'.ucwords($layout['layout']).'</option>';
        }

        $return[] = '</select>'; // Close the select element

        // Return the select element with all of the options
        return implode('', $return);
    }


    /**
     * Gets themes from the database and finds the layouts that belong to them
     *
     * @param string $current
     * @return string
     */
    public function get_selectable_layouts($current = '') {
        try {
            return $this->set_selectable_layouts($current);
        } catch (Exception $e) { $this->Theamus->notify('danger', $e->getMessage()); }
    }


    /**
     * Validates the page form data
     *
     * @param array $args
     * @return array $args
     * @throws Exception
     */
    protected function check_page_args($args = array(), $edit = false) {
        if ($edit) {
            if (!isset($args['id']) || $args['id'] == '' || !is_numeric($args['id'])) {
                throw new Exception('Invalid ID.');
            }
        }

        // Check for a title
        if (!isset($args['title']) || $args['title'] == '') throw new Exception('Invalid page title.');

        // Check for page content
        if (!isset($args['content'])) throw new Exception('Failed to find the page content.');

        // Check for a theme layout
        if (!isset($args['layout'])) throw new Exception('Failed to find the page layout.');

        // Check for groups
        if (!isset($args['groups'])) throw new Exception('Failed to find groups.');

        // Check for navigation
        if (!isset($args['navigation'])) throw new Exception('Failed to find page navigation.');

        // Define the page alias
        $args['alias'] = strtolower(preg_replace("/[^A-Za-z0-9-]/", "", str_replace(' ', '-', $args['title'])));

        // Check the page title and alias length
        if (strlen($args['title']) > 200 || strlen($args['alias']) > 250) throw new Exception('Page title is too long.');

        // Define the groups
        $args['groups'] = $args['groups'] == '' ? 'everyone' : strtolower($args['groups']);

        return $args; // Return the arg data
    }


    /**
     * Creates a Theamus Link for a page
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    protected function create_link($args) {
        // Query the database, creating a new link
        $query = $this->Theamus->DB->insert_table_row(
            $this->Theamus->DB->system_table('links'),
            array('alias'   => substr($args['alias'], 0, 100),
                'text'      => substr($args['title'], 0, 75),
                'path'      => $args['alias'],
                'weight'    => 0,
                'groups'    => $args['groups'],
                'type'      => 'page',
                'location'  => 'main',
                'child_of'  => 0));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to create a link for this page.');
        }

        return true;
    }


    /**
     * Creates a Theamus Page
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function create_page($args) {
        // Check for an administrator with the ability to create pages
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('create_pages')) {
            die('You must be an administrator to do that.');
        }

        // Check the arguments for correctness
        $args = $this->check_page_args($args);

        // Create a link (if neccesary)
        if ($args['create_link']) $this->create_link($args);

        // Query the database, adding the information to it
        $query = $this->Theamus->DB->insert_table_row(
            $this->Theamus->DB->system_table('pages'),
            array('alias'        => $args['alias'],
                'title'          => $args['title'],
                'raw_content'    => $args['content'],
                'views'          => 0,
                'permanent'      => 0,
                'groups'         => $args['groups'],
                'theme'          => $args['layout'],
                'navigation'     => $args['navigation']));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to create page.');
        }

        return true;
    }


    /**
     *
     * @param array $page_groups
     * @return string $return
     */
    public function get_group_options($page_groups = array()) {
        $return = array(); // Initialize the return array

        // Query the database for groups
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('groups'),
            array('alias', 'name'));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            $return[] = '<option>Failed to find groups.</option>';
        } else {
            // Define the query information
            $results = $this->Theamus->DB->fetch_rows($query);

            // Loop through all groups, showing as options
            foreach (isset($results[0]) ? $results : array($results) as $group) {
                if (empty($page_groups)) {
                    $selected = $group['alias'] == 'everyone' ? 'selected' : '';
                } else {
                    $selected = in_array($group['alias'], $page_groups) ? 'selected' : '';
                }

                $return[] = '<option '.$selected.' value="'.$group['alias'].'">'.$group['name'].'</option>';
            }
        }

        return implode('', $return); // Return the array of options as a string
    }


    /**
     * Gets information about a page from the database
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get_page($id = 0) {
        // Check the ID
        if ($id == '' || $id == 0 || !is_numeric($id)) throw new Exception('Invalid ID.');

        // Query the database for the page
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('pages'),
            array(),
            array('operator' => '',
                'conditions' => array('id' => $id)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query erro

            throw new Exception('Failed to find page.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find page.');

        // Define the information from the query
        return $this->Theamus->DB->fetch_rows($query);
    }


    /**
     * Updates a link in the database
     *
     * @param array $args
     * @param int $link_id
     * @throws Exception
     */
    protected function update_link($args, $link_id) {
        $query_conditions = array(); // Initialize the query conditions array

        // Loop through all of the link ID's and add them to the condition array
        foreach ($link_id as $id) $query_conditions[] = array('operator' => '', 'conditions' => array('id' => $id['id']));

        // Query the database, updating the link
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('links'),
            array('path' => substr($args['alias'], 0, 100),
                'text'   => substr($args['title'], 0, 75)),
            array('operator' => 'AND',
                'conditions' => $query_conditions));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Undo the any changes thus far
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            throw new Exception('Failed to update the page link');
        }

        return;
    }


    /**
     * Gets a link with the path of a page from the database
     *
     * @param array $args
     * @return int
     * @throws Exception
     */
    protected function get_page_link($args) {
        // Query the database for a page link
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('links'),
            array('id'),
            array('operator' => '',
                'conditions' => array('[%]path' => substr($args['alias'], 0, 100).'%')));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to find associated page links.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) return 0;

        // Define the link information
        $link = $this->Theamus->DB->fetch_rows($query);

        $links = array(); // Initialize the links array

        // Create an array for the links to update
        foreach (isset($link[0]) ? $link : array($link) as $id) $links[] = $id;

        // Return the link id
        return $links;
    }


    /**
     * Saves page information to the database
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function save_page($args) {
        // Check for an administrator with the ability to create pages
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('edit_pages')) {
            die('You must be an administrator to do that.');
        }

        // Check the arguments for correctness
        $args = $this->check_page_args($args, true);

        // Define the page information
        $page = $this->get_page($args['id']);

        // Try to find the page link in the database
        $page_link = $this->get_page_link($page);

        // Enable transaction in case something goes wrong
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);

        // Check for associated links and update them if there are any
        if (!empty($page_link)) $this->update_link($args, $page_link);

        // Query the database, saving the page information
        $query = $this->Theamus->DB->update_table_row(
            $this->Theamus->DB->system_table('pages'),
            array('alias'        => $args['alias'],
                'title'          => $args['title'],
                'raw_content'    => $args['content'],
                'groups'         => $args['groups'],
                'theme'          => $args['layout'],
                'navigation'     => $args['navigation']),
            array('operator' => '',
                'conditions' => array('id' => $args['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Undo the any changes thus far
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            throw new Exception('Failed to save the page.');
        }

        // Commit any changes made to the database
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();
        return true; // Return true!
    }


    /**
     * Removes any links associated to a page from the database
     *
     * @param array $link_id
     * @return
     * @throws Exception
     */
    protected function remove_link($link_id) {
        $query_conditions = array(); // Initialize the query conditions array

        // Loop through all of the link ID's and add them to the condition array
        foreach ($link_id as $id) $query_conditions[] = array('operator' => '', 'conditions' => array('id' => $id['id']));

        // Query the database, deleting the link
        $query = $this->Theamus->DB->delete_table_row(
            $this->Theamus->DB->system_table('links'),
            array('operator' => 'AND',
                'conditions' => $query_conditions));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Undo the any changes thus far
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            throw new Exception('Failed to update the page link');
        }

        return;
    }


    /**
     * Deletes a page from the database
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function remove_page($args) {
        // Define the page information
        $page = $this->get_page($args['id']);

        $page_link = $this->get_page_link($page); // Find all associated links

        // Enable transaction in case something goes wrong
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);

        // Remove any associated links, if possible
        if ($args['remove_links'] && !empty($page_link)) $this->remove_link($page_link);

        // Query the database, updating the link
        $query = $this->Theamus->DB->delete_table_row(
            $this->Theamus->DB->system_table('pages'),
            array('operator' => 'AND',
                'conditions' => array('id' => $page['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            // Undo the any changes thus far
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();

            throw new Exception('Failed to delete the page.');
        }

        // Commit any changes made to the database
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();
        return true; // Return true!
    }
}