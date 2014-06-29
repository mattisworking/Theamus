<?php

class Appearance {
    private $tDataClass;
    private $tData;
    private $tFiles;

    public function __construct() {
        $this->initialize();
        return;
    }

    public function __destruct() {
        $this->tDataClass->disconnect();
        return;
    }

    private function initialize() {
        $this->tDataClass = new tData();
        $this->tData = $this->tDataClass->connect();
        $this->tDataClass->prefix = $this->tDataClass->get_system_prefix();
        $this->tFiles = new tFiles();
        return;
    }

    private function get_id_get() {
        $id = filter_input(INPUT_GET, "id");
        if (!isset($id)) $id = filter_input(INPUT_POST, "id");
        if (isset($id) && is_numeric($id)) return $id;
        else throw new Exception("Cannot retrieve Theme ID.");
        return;
    }

    private function get_file_get() {
        $file = filter_input(INPUT_GET, "file");
        if (isset($file)) return $file.".php";
        else throw new Exception("Cannot retrieve requested settings file.");
    }

    private function get_db_theme() {
        $id = $this->get_id_get();
        $q = $this->tData->query("SELECT * FROM `".$this->tDataClass->prefix."themes` WHERE `id`='$id'");
        if ($q && $q->num_rows > 0) return $q->fetch_assoc();
        else throw new Exception("Cannot retrieve Theme database information.");
        return;
    }

    private function get_settings_config() {
        $theme = $this->get_db_theme();
        $path = path(ROOT."/themes/".$theme['alias']."/settings/settings.json");
        if (file_exists($path)) return json_decode(file_get_contents($path), true);
        else throw new Exception("Cannot retrieve Theme settings information file.");
    }

    private function check_settings_config() {
        $settings = $this->get_settings_config();
        $required = array("class", "tabs");
        foreach ($required as $r) $ret[] = isset($settings[$r]) ? true : false;
        return in_array(false, $ret) ? false : true;
    }

    private function settings_config() {
        if ($this->check_settings_config()) return $this->get_settings_config();
        else throw new Exception("Required fields are missing in the settings information file.");
    }

    private function check_settings_path() {
        $file = $this->get_file_get();
        $theme = $this->get_db_theme();
        $path = path(ROOT."/themes/".$theme['alias']."/settings/views/$file");
        if (file_exists($path)) return $path;
        else throw new Exception("Cannot find this settings file.");
    }

    private function check_class_variables() {
        $settings = $this->settings_config();
        $required = array("file", "init", "class");
        foreach ($required as $r) $ret[] = isset($settings['class'][$r]) ? true : false;
        return in_array(false, $ret) ? false : true;
    }

    private function get_class_variables() {
        $class_settings = $this->check_class_variables();
        if ($class_settings) {
            $sc = $this->settings_config();
            return $sc['class'];
        }
        else throw new Exception("Required class fields are missing in the settings information file.");
    }

    private function get_class_file() {
        $class_settings = $this->get_class_variables();
        $theme = $this->get_db_theme();
        $path = path(ROOT."/themes/".$theme['alias']."/settings/".$class_settings['file']);
        if (file_exists($path)) include $path;
        else throw new Exception("Cannot retrieve the Theme class file.");
    }

    private function check_class_existance() {
        $class_settings = $this->get_class_variables();
        if (class_exists($class_settings['class'])) return true;
        else throw new Exception("The class name provided does not exist.");
    }

    private function get_theme_class() {
        $settings = $this->settings_config();
        $this->get_class_file();
        if ($this->check_class_existance()) return new $settings['class']['class']();
    }

    private function get_upload_file() {
        $file = $_FILES['file'];
        $name_arr = explode(".", $file['name']);
        if (end($name_arr) !== "zip") throw new Exception("Only ZIP files may be uploaded.");
        else return $file;
    }

    private function clean_temp_folder() {
        $path = ROOT."/features/appearance/temp";
        $files = $this->tFiles->scan_folder($path);
        $folders = $this->tFiles->scan_folder($path, false, "folders");
        foreach ($files as $f) if ($f != path($path."/blank.txt")) unlink($f);
        foreach ($folders as $f) $this->tFiles->remove_folder($f);
    }

    private function clean_theme_folder($f = false) {
        if (!$f) return;
        $this->tFiles->remove_folder(path(ROOT."/themes/".$f));
    }

    private function check_config_layouts($c) {
        if (!isset($c['layouts'])) throw new Exception("There are no layouts defined for this theme.  Is everything set up correctly?");
        $ret['default'] = $ret['error'] = $ret['empty'] = $ret['blank'] = false;
        foreach ($c['layouts'] as $layout) {
            if ($layout['layout'] == "default") $ret['default'] = true;
            if ($layout['layout'] == "error") $ret['error'] = true;
            if ($layout['layout'] == "empty") $ret['empty'] = true;
            if ($layout['layout'] == "blank") $ret['blank'] = true;
        }
        if ($ret['default'] && $ret['error'] && $ret['empty'] && $ret['blank']) return true;
        else throw new Exception("There are required layouts missing that are causing the installation to fail.");
    }

    private function check_config_theme($c) {
        if (!isset($c['theme'])) throw new Exception("The theme information is missing from the configuration file.");
        if (isset($c['theme']['folder']) && isset($c['theme']['name']) && isset($c['theme']['version'])) return true;
        else throw new Exception("There is a field missing from the theme information section in the configuration file.");
    }

    private function check_config_author($c) {
        if (!isset($c['author'])) throw new Exception("The author's information for this theme doesn't exist.  It needs to.");
        $a = $c['author'];
        if (isset($a['name']) && isset($a['alias']) && isset($a['company']) && isset($a['email'])) return true;
        else throw new Exception("There is a field missing from the author's information section in the configuration file.");
    }

    private function check_install_config($c) {
        if (!is_array($c)) return false;
        if (empty($c)) return false;
        $this->check_config_layouts($c);
        if (!isset($c['areas'])) throw new Exception("The required field 'areas' is missing from this theme's configuration file.");
        if (!isset($c['navigation'])) throw new Exception("The required field 'navigation' is missing from this theme's configuration file.");
        if (!isset($c['settings'])) throw new Exception("The required field 'settings' is missing from this theme's configuration file.");
        $this->check_config_theme($c);
        $this->check_config_author($c);
    }

    private function run_theme_installer($t, $info) {
        $path = ROOT."/features/appearance/temp/".trim($t, ".zip");
        if (file_exists(path($path."/install.php"))) {
            include path($path."/install.php");
            if (!function_exists("install_theme")) throw new Exception("The 'install_theme()' function does not exist.");
            install_theme($info);
        }
    }

    private function extract_theme($f, $c) {
        $path = ROOT."/features/appearance/temp/";
        if ($this->tFiles->extract_zip(path($path.$f), path(ROOT."/themes/".$c['theme']['folder']))) return true;
        else {
            $this->clean_temp_folder();
            throw new Exception("There was an issue extracting the theme");
        }
    }

    private function add_theme_db($c) {
        $q = $this->tData->query("INSERT INTO `".$this->tDataClass->prefix."themes` (`alias`, `name`, `active`, `permanent`) VALUES ".
                "('".$c['theme']['folder']."', '".$c['theme']['name']."', 0, 0)");
        if (!$q) {
            $this->clean_theme_folder($c['theme']['folder']);
            throw new Exception("There was an error adding this theme to the database.");
        }
    }

    public function get_tabs() {
        $settings = $this->settings_config();
        return $settings['tabs'];
    }

    public function get_settings_page() {
        $class_settings = $this->get_class_variables();
        ${$class_settings['init']} = $this->get_theme_class();
        include $this->check_settings_path();
    }

    public function load_theme_function($f) {
        $class_settings = $this->get_class_variables();
        ${$class_settings['init']} = $this->get_theme_class();
        if (method_exists(${$class_settings['init']}, $f)) ${$class_settings['init']}->$f();
        else throw new Exception("Method '$f' in class '".$class_settings['init']."' does not exist.");
    }

    public function get_theme_info() {
        return $this->get_db_theme();
    }

    public function upload_theme() {
        if (count($_FILES) == 0) throw new Exception("Please select a file to upload.");
        $file = $this->get_upload_file();
        $path = ROOT."/features/appearance/temp/";
        $temp_name = md5(time()).".zip";
        if (move_uploaded_file($file['tmp_name'], path($path.$temp_name))) {
            return $temp_name;
        } else throw new Exception("The file failed to upload.");
    }

    public function extract_tmp_theme($f) {
        $path = ROOT."/features/appearance/temp/";
        if ($this->tFiles->extract_zip(path($path.$f), path(trim($path.$f, ".zip")))) return true;
        else {
            $this->clean_temp_folder();
            throw new Exception("There was an issue extracting the theme");
        }
    }

    public function prelim_install() {
        $theme = $this->upload_theme();
        $this->extract_tmp_theme($theme);
        $config = $this->get_config($theme);
        $this->check_install_config($config);
        $this->clean_temp_folder();
        return $config;
    }

    public function install_theme() {
        $theme = $this->upload_theme();
        $this->extract_tmp_theme($theme);
        $config = $this->get_config($theme);
        $this->run_theme_installer($theme, $config);
        $this->extract_theme($theme, $config);
        $this->add_theme_db($config);
        $this->clean_temp_folder();
    }

    public function get_config($f = "") {
        $path = ROOT."/features/appearance/temp/".trim($f, ".zip");
        if (file_exists($path."/config.json")) return json_decode(file_get_contents(path($path."/config.json")), true);
        else {
            $this->clean_temp_folder();
            throw new Exception("The configuration file for the uploaded theme does not exist.");
        }
    }

    public function check_config_options($c) {
        $required = array("layouts", "areas", "navigation", "settings", "theme", "author");
        foreach ($required as $r) $ret[] = array_key_exists($r, $c) ? true : false;

        if (in_array(false, $ret)) {
            $this->clean_temp_folder();
            throw new Exception("There is an error in the theme configuration file. It is missing a required field.");
        }
    }

    public function set_active_theme() {
        $id = filter_input(INPUT_GET, "id");
        if (isset($id) && is_numeric($id)) {
            $table = $this->tDataClass->prefix."themes";
            $this->tData->query("UPDATE `$table` SET `active`=0");
            $this->tData->query("UPDATE `$table` SET `active`=1 WHERE `id`='$id'");
            notify("admin", "success", "Active theme updated.");
        } else throw new Exception("Cannot find the Theme ID to make active.");
    }

    public function finalize_update($f) {
        $theme = $this->get_db_theme();
        $from = path(ROOT."/features/appearance/temp/".$f);
        $to = path(ROOT."/themes/".$theme['alias']);
        if ($this->tFiles->extract_zip($from, $to)) notify("admin", "success", "This theme has been updated successfully.");
        else throw new Exception("There was an issue extracting the upload to the final destination.");
        $this->clean_temp_folder();
    }

    public function print_exception($ex) {
        $this->clean_temp_folder();
        notify("admin", "failure", "<strong>Appearance Theme Error:</strong> ".$ex->getMessage());
    }
}