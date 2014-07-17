<?php

class Groups {
    /**
     * Theamus data manipulation class
     *
     * @var object $tData
     */
    protected $tData;


   /**
     * Theamus user class
     *
     * @var object $tUser
     */
    protected $tUser;


    /**
     * Starts the class connecting to classes that will be needed throughout
     *
     * @return
     */
    public function __construct() {
        $this->initialize_variables();
        return;
    }


    /**
     * Connects to the database, defines the user and pagniation classes as well
     *
     * @return
     */
    private function initialize_variables() {
        // Connect to the database
        $this->tData            = new tData();
        $this->tData->db        = $this->tData->connect(true);
        $this->tData->prefix    = DB_PREFIX;

        $this->tUser            = new tUser();  // User data class
        $this->tPages           = new tPages(); // Pagination class
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
            array('Create a New Group', 'create.php', 'Create a New Group')
        );

        $return_tabs = array(); // Empty return array to add to

        // Loop through all of the tabs defined above and assign them to li items/links
        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : ''; // Define the current tab
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'groups-tab\' data-file=\'groups/'.trim($tab[1], '.php').'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        // Return the tabs to the page
        return '<ul>'.implode('', $return_tabs).'</ul>';
    }
}