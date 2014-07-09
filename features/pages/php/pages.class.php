<?php

class Pages {
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
     * Define the pages tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function pages_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('List of Pages', 'index.php', 'Theamus Pages'),
            array('Create a New Page', 'create.php', 'Create a New Page')
        );

        $return_tabs = array(); // Empty return array to add to

        // Loop through all of the tabs defined above and assign them to li items/links
        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : ''; // Define the current tab
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'pages-tab\' data-file=\'pages/'.trim($tab[1], '.php').'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        // Return the tabs to the page
        return '<ul>'.implode('', $return_tabs).'</ul>';
    }

    private function get_current_theme() {
        $q = $this->tData->select_from_table($this->tData->prefix.'themes', array('alias'), array(
            'operator'      => '',
            'conditions'    => array('active' => '1')));

        if (!$q) throw new Exception("Error finding the active theme.");

        $r = $this->tData->fetch_rows($q);
        return $r['alias'];
    }

    private function get_theme_options() {
        $alias = $this->get_current_theme();
        $config_path = path(ROOT."/themes/$alias/config.json");
        if (file_exists($config_path)) {
            $layouts = json_decode(file_get_contents($config_path))->layouts;
            foreach ($layouts as $layout) {
                $ret[$layout->layout]['layout'] = $layout->layout;
                $ret[$layout->layout]['nav'] = $layout->allow_nav == true ? "true" : "false";
            }
            return $ret;
        } else throw new Exception("Error locating the theme configuration file.");
    }

    private function set_selectable_layouts($current) {
        $layouts = $this->get_theme_options();
        if (!empty($layouts)) {
            $ret[] = "<select class='form-control' name='layout' onchange='show_nav_options();'>";
            foreach($layouts as $layout) {
                $select = $current == $layout['layout'] ? "selected" : "";
                $ret[] = "<option value='".$layout['layout']."' $select data-nav='".$layout['nav']."'>".ucwords($layout['layout'])."</option>";
            }
            $ret[] = "</select>";

            return implode("", $ret);
        } else throw new Exception("There are no layouts for this theme, the default has been selected.");
    }

    public function get_selectable_layouts($current = "") {
        try {
            return $this->set_selectable_layouts($current);
        } catch (Exception $e) { echo $e; }
    }
}