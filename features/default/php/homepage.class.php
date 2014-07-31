<?php

class HomePage {
    public $page_content = "";
    public function __construct() {
        $this->initialize();
        return;
    }

    public function __destruct() {
        $this->tData->disconnect();
        return;
    }

    private function initialize() {
        $this->tData = new tData();
        $this->tData->db = $this->tData->connect();
        $this->tData->prefix = DB_PREFIX;
        $this->tUser = new tUser();
        return;
    }

    private function get_system_home() {
        $q = $this->tData->db->query("SELECT `home` FROM `".$this->tData->prefix."settings`");
        if (!$q) throw new Exception("Cannot find the home page in the settings table.");
        if ($q->num_rows == 0) throw new Exception("There is no home page column in the settings table.");
        $r = $q->fetch_assoc();
        return $r['home'];
    }

    private function decode_home($given = false) {
        $d = $this->tData->t_decode(!$given ? $this->get_system_home() : $given);
        if ($d[0] != "homepage") throw new Exception("Invalid home page information.");
        else return $d;
    }

    private function check_user_login() {
        if ($this->tUser->user != false) return true;
        return false;
    }

    private function get_user_groups($g) {
        $ret = array();
        $gs = explode(",", $g);
        foreach ($gs as $g) {
            $g = $this->tData->db->real_escape_string($g);
            $q = $this->tData->db->query("SELECT `home_override` FROM `".$this->tData->prefix."groups` WHERE `alias`='$g'");
            if (!$q) continue;
            if ($q->num_rows == 0) continue;
            $r = $q->fetch_assoc();
            $ret[] = $r['home_override'];
        }
        return $ret;
    }

    private function check_group_home() {
        if (!$this->tUser->user) return false;
        else {
            $ret = array();
            foreach ($this->get_user_groups($this->tUser->user['groups']) as $g) {
                if ($g !== "false") $ret[] = $this->tData->t_decode($g);
            }
            return $ret;
        }
    }

    private function handle_type($given = false) {
        $gh = $this->check_group_home();
        $dh = $this->decode_home();
        $type = $given == false ? $dh['type'] : $given['after-type'];
        if (count($gh) >= 1 && $gh != false) {
            $type = $gh[0]['type'];
            $given = $gh[0];
        }
        switch ($type) {
            case "page": return $this->handle_page($given);
            case "feature": return $this->handle_feature($given);
            case "custom": return $this->handle_custom($given);
            case "require-login": return $this->handle_login();
            case "session": return $this->handle_session();
            default: throw new Exception("Unknown homepage type.");
        }
    }

    private function handle_page($given = false) {
        $h = $given == false ? $this->decode_home() : $given;
        if (!array_key_exists("id", $h)) throw new Exception("No page ID defined.");
        $q = $this->tData->db->query("SELECT * FROM `".$this->tData->prefix."pages` WHERE `id`='".$h['id']."'");
        if (!$q) throw new Exception("Error querying the database for the home page.");
        if ($q->num_rows == 0) throw new Exception("Cannot find the home page in the database.");
        $p = $q->fetch_assoc();
        $this->page_content = $p['content'];
        if ($p['navigation'] == "") $navigation = "";
        else {
            $navigation = array();
            $nav = explode(",", $p['navigation']);
            foreach($nav as $n) {
                $link = explode("::", $n);
                $navigation[$link[0]] = isset($link[1]) ? $link[1] : "";
            }
            $p['navigation'] = $navigation;
        }
        return $p;
    }

    private function handle_feature($given = false) {
        $h = $given == false ? $this->decode_home() : $given;
        if (!array_key_exists("id", $h)) throw new Exception("No feature ID defined.");
        if (!array_key_exists("file", $h)) throw new Exception("No feature file defined.");
        $q = $this->tData->db->query("SELECT * FROM `".$this->tData->prefix."features` WHERE `id`='".$h['id']."'");
        if (!$q) throw new Exception("Error querying the database for the home feature.");
        if ($q->num_rows == 0) throw new Exception("Cannot find the home feature in the database.");
        $f = $q->fetch_assoc();
        header("Location: ".$f['alias']."/".$h['file']);
    }

    private function handle_custom($given = false) {
        $h = $given == false ? $this->decode_home() : $given;
        if (!array_key_exists("url", $h)) throw new Exception("No custom URL to go to.");
        header("Location: ".$h['url']);
    }

    private function handle_login() {
        $h = $this->decode_home();
        if ($this->check_user_login()) return $this->handle_type($h);
        else header("Location: accounts/login/");
    }

    private function handle_session() {
        if ($this->check_user_login()) $ret =$this->check_session_vars("after");
        else $ret = $this->check_session_vars('before');
        return $this->handle_type($ret);
    }

    private function check_session_vars($t) {
        $h = $this->decode_home();
        if (!array_key_exists("$t-type", $h)) throw new Exception("Home page type not found.");
        if (!array_key_exists("$t-id", $h)) throw new Exception("Home page id not found.");

        $ret['after-type'] = $h[$t.'-type'];
        $ret['id'] = $h[$t.'-id'];
        $ret['file'] = array_key_exists("$t-file", $h) ? $h[$t.'-file'] : "";
        $ret['url'] = array_key_exists("$t-url", $h) ? $h[$t.'-url'] : "";
        return $ret;
    }

    public function redirect() {
        try { return $this->handle_type(); }
        catch (Exception $ex) { die("<strong>Theamus home page error:</strong> ".$ex->getMessage()); }
    }
}