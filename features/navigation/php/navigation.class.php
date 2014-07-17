<?php

class Navigation {
    private $tDataClass;
    private $tData;

    public function __construct() {
        $this->initialize_variables();
        return;
    }

    public function __destruct() {
        $this->tDataClass->disconnect();
        return;
    }

    private function initialize_variables() {
        $this->tDataClass           = new tData();
        $this->tData                = $this->tDataClass->connect();
        $this->tDataClass->prefix   = DB_PREFIX;
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
            array('List of Navigation Links', 'index.php', 'Theamus Navigation'),
            array('Create a New Link', 'create.php', 'Create a New Link')
        );

        $return_tabs = array(); // Empty return array to add to

        // Loop through all of the tabs defined above and assign them to li items/links
        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : ''; // Define the current tab
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'navigation-tab\' data-file=\'navigation/'.trim($tab[1], '.php').'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        // Return the tabs to the page
        return '<ul>'.implode('', $return_tabs).'</ul>';
    }

    private function get_current_theme() {
        $q = $this->tData->query("SELECT * FROM `".$this->tDataClass->prefix."themes` WHERE `active`=1");
        return $q->fetch_assoc();
    }

    private function get_nav_positions() {
        $theme = $this->get_current_theme();
        $path = path(ROOT."/themes/".$theme['alias']."/config.json");
        if (file_exists($path)) {
            $config = json_decode(file_get_contents($path));
            if (isset($config->navigation)) return $config->navigation;
        }
        return false;
    }

    public function get_positions_select($current = "") {
        $pos = $this->get_nav_positions();
        if ($pos) {
            foreach ($pos as $p) {
                $s = $current == $p ? "selected" : "";
                $ret[] = "<option value='$p' $s>".ucwords(str_replace("_", " ", $p))."</option>";
            }
            return implode("", $ret);
        }
        return "<option value='main'>Main</option>";
    }

    public function get_children_select($child_of = 0) {
        $ret = array("<option value='0'>Not a Child</option>");
        $q = $this->tData->query("SELECT * FROM `".$this->tDataClass->prefix."links` ORDER BY `text` ASC");
        while ($row = $q->fetch_assoc()) {
            $s = $row['id'] == $child_of ? "selected" : "";
            $ret[] = "<option value='".$row['id']."' $s>".$row['text']."</option>";
        }
        return implode("", $ret);
    }
}