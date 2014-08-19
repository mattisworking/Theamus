<?php

class HomePage {
    public $page_content = '';

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
     * Decodes the home page
     *
     * @param string $given
     * @return array
     * @throws Exception
     */
    private function decode_home($given = false) {
        // Decode the homepage information from the database or given
        $decoded = $this->Theamus->DB->t_decode(!$given ? $this->Theamus->settings['home'] : $given);

        // Check if it decoded properly
        if ($decoded[0] != 'homepage') throw new Exception('Invalid home page information.');

        return $decoded; // Return the decoded information
    }


    /**
     * Gets all of the potential homepages for a user from the database based
     *  on the group he/she is in
     *
     * @param string $user_groups
     * @return array
     */
    private function get_user_groups($user_groups) {
        $ret = array(); // Initialize the return array

        // Loop through all of the user groups
        foreach (explode(',', $user_groups) as $group) {
            // Query the database for this group
            $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table('groups'),
                array('home_override'),
                array('operator'  => '',
                    'conditions'  => array('alias' => $group)));

            // Check the query for errors
            if (!$query) continue;

            // Check the query for results
            if ($this->Theamus->DB->count_rows($query) == 0) continue;

            // Define the information from the database
            $result = $this->Theamus->DB->fetch_rows($query);

            // Add the override to the database
            $ret[] = $result['home_override'];
        }

        return $ret; // Return the potential home pages
    }


    /**
     * Checks for the possible home pages that is assigned to a group that the user is in
     *
     * @return boolean|array
     */
    private function check_group_home() {
        // Check for a user
        if (!$this->Theamus->User->user) return false;
        else {
            $ret = array(); // Initialize the return array

            // Loop through all of the potential group homepages
            foreach ($this->get_user_groups($this->Theamus->User->user['groups']) as $group) {
                // If there is a home page to go to, decode it and add it to the return array
                if ($group != 'false') $ret[] = $this->Theamus->DB->t_decode($group);
            }

            return $ret; // Return the home pages
        }
    }


    /**
     * Handles the home page based on the type it is
     *
     * @param boolean|array $given
     * @return array
     * @throws Exception
     */
    private function handle_type($given = false) {
        $group_home = $this->check_group_home(); // Define the group home pages
        $decoded_home = $this->decode_home(); // Define the decoded home pages

        // Define the type of phome page
        $type = $given == false ? $decoded_home['type'] : $given['after-type'];

        // Check the count and group home to be something
        if (count($group_home) >= 1 && $group_home != false) {
            $type = $group_home[0]['type']; // Define the new type
            $given = $group_home[0]; // Define the new data to be passed on
        }

        // Switch through all of the types to handle stuffs
        switch ($type) {
            case 'page': return $this->handle_page($given);
            case 'feature': return $this->handle_feature($given);
            case 'custom': return $this->handle_custom($given);
            case 'require-login': return $this->handle_login();
            case 'session': return $this->handle_session();
            default: throw new Exception('Unknown homepage type.');
        }
    }


    /**
     * Handles the home page if it were a static page
     *
     * @param boolena|array $given
     * @return array
     * @throws Exception
     */
    private function handle_page($given = false) {
        // Define the home based on the given data or the database
        $home = $given == false ? $this->decode_home() : $given;

        // Check for a page ID to load
        if (!array_key_exists('id', $home)) throw new Exception('No page ID defined.');

        // Query the database for the page information
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('pages'),
            array(),
            array(
                'operator'   => '',
                'conditions' => array('id' => $home['id'])));

        // Check the query for errors
        if (!$query) throw new Exception('Error querying the database for the home page.');

        // Check for any returned results from the query
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Cannot find the home page in the database.');

        // Define the page information from the database
        $page = $this->Theamus->DB->fetch_rows($query);

        // Define the page content for the class
        $this->page_content = $this->Theamus->Parsedown->text($page['raw_content']);

        // Check for any navigation the home page might have
        if ($page['navigation'] != '') {
            $navigation = array(); // Initialize the navigation array

            // Loop through all of the navigation items from the table column
            foreach (explode(',', $page['navigation']) as $nav_item) {
                // Define the link information based on the nav item
                $link = explode('::', $nav_item);

                // Add the link information to the navigation array
                $navigation[$link[0]] = isset($link[1]) ? $link[1] : '';
            }

            $page['navigation'] = $navigation; // Set the page navigation to be a more usable format
        }

        return $page; // Return the page information
    }


    /**
     * Handles the home page if it were a feature
     *
     * @param boolean|array $given
     * @throws Exception
     */
    private function handle_feature($given = false) {
        // Define the home page from the given data or the database
        $home = $given == false ? $this->decode_home() : $given;

        // Check for an ID key in the home information
        if (!array_key_exists('id', $home)) throw new Exception('No feature ID defined.');

        // Check for a FILE key in the home information
        if (!array_key_exists('file', $home)) throw new Exception('No feature file defined.');

        // Query the database for this feature
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('features'),
            array(),
            array('operator' => '',
                'conditions' => array('id' => $home['id'])));

        // Check the query for errors
        if (!$query) throw new Exception('Error querying the database for the home feature.');

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Cannot find the home feature in the database.');

        // Define the feature information from the database
        $feature = $this->Theamus->DB->fetch_rows($query);

        // Relocate to the feature
        header('Location: '.$this->Theamus->base_url.$feature['alias'].'/'.$home['file']);
    }


    /**
     * Handles the home page if it were a custom url
     *
     * @param boolean|array $given
     * @throws Exception
     */
    private function handle_custom($given = false) {
        // Define the home page from the given data or the database
        $home = $given == false ? $this->decode_home() : $given;

        // Check for the URL key in the homepage information
        if (!array_key_exists('url', $home)) throw new Exception('No custom URL to go to.');

        // Relocate to the url
        header('Location: '.$home['url']);
    }


    /**
     * Sends the user to the login page, if required
     *
     * @return array
     */
    private function handle_login() {
        // Decode the home page from the database
        $home = $this->decode_home();

        // Check for a logged in user
        if ($this->Theamus->User->user) return $this->handle_type($home);

        // Relocate to the login page
        else header('Location: accounts/login/');
    }


    /**
     * Handles the home page based on a user's session (logged in/out)
     *
     * @return array
     */
    private function handle_session() {
        // Check for a logged in user and handle when they are logged in
        if ($this->Theamus->User->user) $ret = $this->check_session_vars('after');

        // Handle when they are logged out
        else $ret = $this->check_session_vars('before');

        // Return the handle, based on the user's session
        return $this->handle_type($ret);
    }


    /**
     * Checks the session variables to help handle the session home page
     *
     * @param string $time
     * @return array
     * @throws Exception
     */
    private function check_session_vars($time) {
        // Decode the homepage from the database
        $home = $this->decode_home();

        // Check for the TYPE key in the homepage info
        if (!array_key_exists($time.'-type', $home)) throw new Exception('Home page type not found.');

        // Check for the ID key in the homepage information
        if (!array_key_exists($time.'-id', $home)) throw new Exception('Home page id not found.');

        // Define the information that will help show the correct page
        $ret['after-type']  = $home[$time.'-type'];
        $ret['id']          = $home[$time.'-id'];
        $ret['file']        = array_key_exists($time.'-file', $home) ? $home[$time.'-file'] : '';
        $ret['url']         = array_key_exists($time.'-url', $home) ? $home[$time.'-url'] : '';

        return $ret; // Return the correct session information
    }


    /**
     * Redirects the user to the appropriate home page
     *
     * @return array
     */
    public function redirect() {
        try { return $this->handle_type(); }
        catch (Exception $ex) { die('<strong>Theamus home page error:</strong> '.$ex->getMessage()); }
    }
}