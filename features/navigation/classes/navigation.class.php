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