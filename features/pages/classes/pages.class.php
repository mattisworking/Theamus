<?php

class Pages {
    public function __construct() {
        $this->initialize_variables();
    }

    public function __destruct() {
        $this->tDataClass->disconnect();
        return;
    }

    private function initialize_variables() {
        $this->tDataClass = new tData();
        $this->tData = $this->tDataClass->connect();
        $this->tDataClass->prefix = DB_PREFIX;
        return;
    }

    private function get_current_theme() {
        $q = $this->tData->query("SELECT `alias` FROM `".$this->tDataClass->prefix."themes` WHERE `active`=1");
        if ($q) {
            $r = $q->fetch_assoc();
            return $r['alias'];
        }
        else throw new Exception("Error finding the active theme.");
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
            $ret[] = "<label class='admin-selectlabel'><select name='layout' onchange='show_nav_options();'>";
            foreach($layouts as $layout) {
                $select = $current == $layout['layout'] ? "selected" : "";
                $ret[] = "<option value='".$layout['layout']."' $select data-nav='".$layout['nav']."'>".ucwords($layout['layout'])."</option>";
            }
            $ret[] = "</select></label>";

            return implode("", $ret);
        } else throw new Exception("There are no layouts for this theme, the default has been selected.");
    }

    public function get_selectable_layouts($current = "") {
        try {
            return $this->set_selectable_layouts($current);
        } catch (Exception $e) { echo $e; }
    }
}