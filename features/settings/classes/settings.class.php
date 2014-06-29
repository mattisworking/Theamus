<?php

class Settings {
    protected $update_server = "http://theamus.com/release-manager/";
    protected $update_server_path = "http://theamus.com/features/release-manager/packages/";

    protected $return = array(
        "error" => array("status" => 0, "message" => ""),
        "response" => array("data" => "")
    );

    protected $tData;
    protected $tFiles;

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
        $this->tFiles = new tFiles();
        return;
    }

    private function get_system_home() {
        $q = $this->tData->db->query("SELECT `home` FROM `".$this->tData->prefix."settings`");
        if (!$q) throw new Exception("Cannot find the home page in the settings table.");
        if ($q->num_rows == 0) throw new Exception("There is no home page column in the settings table.");
        $r = $q->fetch_assoc();
        return $r['home'];
    }

    private function decode_home() {
        $d = $this->tData->t_decode($this->get_system_home());
        if ($d[0] != "homepage") throw new Exception("Invalid home page information.");
        else return $d;
    }

    private function get_db_rows($w, $id = false) {
        $where = $id == false ? "" : "WHERE `id`='$id'";
        $q = $this->tData->db->query("SELECT * FROM `".$this->tData->prefix."$w` $where");
        if (!$q) throw new Exception("Error querying database for $w.");
        if ($q->num_rows == 0) throw new Exception("There are no $w to show.");
        while ($row = $q->fetch_assoc()) $ret[] = $row;
        return $ret;
    }

    protected function get_system_info() {
        $q = $this->tData->db->query("SELECT * FROM `".$this->tData->prefix."settings`");
        if (!$q) throw new Exception("There was an issue querying the database for the custom settings");
        return $q->fetch_assoc();
    }

    public function get_home_info() {
        return $this->decode_home();
    }

    public function get_pages_select($h) {
        $id = $h['type'] == "page" ? $h['id'] : 0;
        try { $ps = $this->get_db_rows("pages"); }
        catch (Exception $ex) { echo $ex->getMessage(); }

        $ret[] = "<label class='admin-selectlabel'><select name='page-id'>";
        foreach ($ps as $p) {
            $s = $p['id'] == $id ? "selected" : "";
            $ret[] = "<option value='".$p['id']."' $s>".$p['title']."</option>";
        }
        $ret[] = "</select></label>";
        return implode("", $ret);
    }

    public function get_features_select($h) {
        $id = $h['type'] == "feature" ? $h['id'] : 0;
        try { $fs = $this->get_db_rows("features"); }
        catch (Exception $ex) { echo $ex->getMessage(); }

        $ret[] = "<label class='admin-selectlabel'><select name='feature-id'>";
        foreach ($fs as $f) {
            $s = $f['id'] == $id ? "selected" : "";
            $ret[] = "<option value='".$f['id']."' $s>".$f['name']."</option>";
        }
        $ret[] = "</select></label>";
        return implode("", $ret);
    }

    public function get_feature_files_select($id) {
        $h = $this->decode_home();
        try { $f = $this->get_db_rows("features", $id); }
        catch (Exception $ex) { echo $ex->getMessage(); }
        $path = path(ROOT."/features/".$f[0]['alias']."/views");
        $files = $this->tFiles->scan_folder($path, $path);
        $ret[] = "<label class='admin-selectlabel'><select name='feature-file'>";
        foreach ($files as $f) {
            $name = ucwords(str_replace(".php", "", str_replace("/", " / ", str_replace("_", " ", str_replace("-", " ", $f)))));
            if (array_key_exists("file", $h) && $h['file'] != "") $s = $h['file'].".php" == $f ? "selected" : "";
            elseif ($f == "index.php") $s = "selected";
            else $s = "";

            $ret[] = "<option value='".trim($f, ".php")."' $s>$name</option>";
        }
        $ret[] = "</select></label>";
        return implode("", $ret);
    }

    public function get_site_name() {
        $info = $this->get_system_info();
        return urldecode($info['name']);
    }

    public function get_session_value($h, $ba) {
        $ret[] = "$ba-type=\"".$h[$ba.'-type']."\";";
        if ($h[$ba.'-type'] == "page") $ret[] = "$ba-id=\"".$h[$ba.'-id']."\";";
        if ($h[$ba.'-type'] == "feature") {
            $ret[] = "$ba-id=\"".$h[$ba.'-id']."\";";
            $ret[] = "$ba-file=\"".$h[$ba.'-file']."\";";
        }
        if ($h[$ba.'-type'] == "url") $ret[] = "$ba-id=\"".$h[$ba.'-url']."\";";
        return implode("", $ret);
    }

    public function save_customization() {
        $post = filter_input_array(INPUT_POST);

        if (!isset($post['name']) || $post['name'] == "") throw new Exception("Please fill out the 'Site Name' field.");
        else $name = $this->tData->db->real_escape_string(urldecode($post['name']));

        if (!isset($post['home-page']) || $post['home-page'] == "") throw new Exception("Please choose a home page.");
        else $home = $this->tData->db->real_escape_string(urldecode($post['home-page']));

        $q = $this->tData->db->query("UPDATE `".$this->tData->prefix."settings` SET `name`='$name', `home`='$home'");
        if (!$q) throw new Exception("There was an error updating the settings database.");
        else return true;
    }


    /**
     * Cleans all of the contents out of the temp directory
     */
    protected function clean_temp_folder() {
        // Define the path to the temp directory and get the files/folders from it
        $temp_directory = ROOT."/features/settings/temp";
        $temp_files     = $this->tFiles->scan_folder($temp_directory);
        $temp_folders   = $this->tFiles->scan_folder($temp_directory, false, "folders");

        // Loop through all of the files and folders, removing them
        foreach ($temp_files as $file) if ($file != path($temp_directory."/blank.txt")) unlink($file);
        foreach ($temp_folders as $folder) $this->tFiles->remove_folder($folder);
    }


    protected function response_error($message) {
        $this->return['error'] = array("status" => 1, "message" => notify("admin", "failure", $message, "", true));
    }


    protected function get_update_info() {
        // Get the update information from the update server
        $info = $this->tData->api(array(
            "type"  => "get",
            "url"   => $this->update_server."update-info",
            "method"=> array("Releases", "get_update_info"),
            "key"   => "dQPlembXjBfGvmCqH0Cot9uMeKAbRkTdr6ysWK1V50U="
        ));

        return $info;
    }


    protected function download_update() {
        // Define the temp directory and a temporary filename
        $temp_directory = ROOT."/features/settings/temp/";
        $temp_filename = md5(time());

        // Get the update information
        $info = $this->get_update_info();

        // Define the options for cURL
        $ch_options = array(
            CURLOPT_URL             => $this->update_server_path.$info['response']['data']['file'],
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_FOLLOWLOCATION  => true
        );

        // Download the file
        $ch = curl_init();
        curl_setopt_array($ch, $ch_options);
        $data = curl_exec($ch);

        // Get the http status
        $ch_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check the return status and return as necessary
        if ($ch_status == "200") {
            // Create the file
            $file = fopen(path($temp_directory.$temp_filename.".zip"), 'w+');
            fputs($file, $data);
            fclose($file);

            // Check if the newly created file exists and return
            if (!file_exists(path($temp_directory.$temp_filename.".zip"))) {
                throw new Exception("Something went wrong creating the downloaded temp file.");
            } else {
                return $temp_filename;
            }
        } else {
            throw new Exception("There was an error downloading the master repo.");
        }
    }


    /**
     * Gets and checks the information about the uploaded file
     *
     * @return array
     * @throws Exception
     */
    private function get_uploaded_file() {
        // Check for the file in the files array
        if (count($_FILES) == 0) throw new Exception("Choose a file to upload.");
        if (!isset($_FILES['file'])) throw new Exception("There was an error finding the uploaded file.");

        // Check the filetype - zip files only
        $uploaded_file = $_FILES['file'];
        $uploaded_filename_array = explode(".", $uploaded_file['name']);
        if (end($uploaded_filename_array) != "zip") throw new Exception("This type of file can't be uploaded in this situation.");


        return $uploaded_file; // Return the file/information
    }


    /**
     * Uploads a file to the temp directory
     *
     * @param array $file
     * @return string $temp_filename
     * @throws Exception
     */
    private function upload_file($file) {
        // Define the temp directory and filename
        $temp_directory = ROOT."/features/settings/temp/";
        $temp_filename = md5(time());

        // Try to upload the file to the temp directory
        if (move_uploaded_file($file['tmp_name'], path($temp_directory.$temp_filename.".zip"))) {
            return $temp_filename;
        } else {
            throw new Exception("There was an issue moving the uploaded file.");
        }
    }


    /**
     * Extracts the uploaded file to the relevant location
     *
     * @param string $filename
     * @param string $type
     * @return boolean
     * @throws Exception
     */
    protected function extract_update($filename = "", $type = "temp") {
        // Check the filename and define the extraction directory
        if ($filename == "") throw new Exception("The zip file cannot be extracted.  The filename is incorrect.");
        $temp_directory = ROOT."/features/settings/temp/";
        $extract_directory = $type == "temp" ? $temp_directory.$filename : ROOT."/";

        // Extract the files
        if (!$this->tFiles->extract_zip(path($temp_directory.$filename.".zip"), path($extract_directory))) {
            throw new Exception("There was an issue when extracting the uploaded file.");
        }
        return true;
    }


    /**
     * Gets update information from the update directory and returns it if possible
     *
     * @return array
     * @throws Exception
     */
    protected function get_update_information($filename = "") {
        // Define the update information file
        $temp_directory = ROOT."/features/settings/temp/$filename/";
        $information_file = path($temp_directory."update/update.json");

        // Check for the existence of the update file
        if (!file_exists($information_file)) {
            throw new Exception("Cannot find the update information file; aborting the update.");
        }

        // Return the json contents of the file as an array
        return json_decode(file_get_contents($information_file), true);
    }


    /**
     * Checks one array for the existance of values in another
     *
     * @param array $given
     * @param array $required
     * @return boolean
     * @throws Exception
     */
    private function validate_array($given = array(), $required = array()) {
        // Check the given and required variables
        if (empty($given) || empty($required) || !is_array($given) || !is_array($required)) {
            throw new Exception("Invalid given or required variables to validate.");
        }

        // Loop through all of the required items, checking them against the given items
        foreach ($required as $item) {
            $catch[] = in_array($item, $given) ? true : false;
        }

        // Return true/false based on the loop above
        return in_array(false, $catch) ? false : true;
    }


    /**
     * Checks the upload information for valid/required fields
     *
     * @param array $update_information
     * @return boolean
     * @throws Exception
     */
    protected function check_update_information($update_information = "") {
        // Check for valid update information
        if ($update_information == "" || !is_array($update_information)) {
            throw new Exception("The provided update information is invalid; aborting the update.");
        }

        // Define and perform checks on the required information
        $required = array("version", "changes", "authors", "run_update_script", "update_files");
        $check_required = $this->validate_array($update_information, $required);

        // Return true/false
        return $check_required == true ? true : false;
    }


    /**
     * Clean the temp folder out, notify the user about the error, then die.
     *
     * @param object $ex
     */
    public function abort_update($ex) {
        $this->clean_temp_folder();
        notify("admin", "failure", "<b>Update Error:</b> ".$ex->getMessage());
        die();
    }


    /**
     * Takes the information from the update information file and allows it to be
     *  accessible to the preliminary update file
     *
     * @param array $update_information
     * @return array $return
     */
    protected function define_update_information($update_information, $filename) {
        // Define all of the information that will be shown during preliminary update
        $return['filename']             = $filename;
        $return['version']              = $update_information['version'];
        $return['run_update_script']    = $update_information['run_update_script'];
        $return['database_changes']     = count($update_information['changes']['database']);
        $return['file_changes']         = count($update_information['changes']['files']);
        $return['bugs']                 = $update_information['changes']['bugs'];
        $return['authors']              = $update_information['authors'];

        // Return the information to be accessible
        return $return;
    }


    /**
     * Runs a preliminary update, to show the user what's going to happen before it does
     */
    public function prelim_update() {
        // Upload and extract the file
        $uploaded_file = $this->get_uploaded_file();
        $uploaded_filename = $this->upload_file($uploaded_file);
        $this->extract_update($uploaded_filename);

        // Perform checks to ensure this is a legit update
        $update_information = $this->get_update_information($uploaded_filename);
        $check_information = $this->check_update_information($update_information);
        if ($check_information) $this->update_information = $this->define_update_information($update_information, $uploaded_filename);
    }


    /**
     * Includes the defined update files
     *
     * @param string $filename
     * @param string|array $files
     * @return boolean
     */
    protected function include_update_files($filename, $files = array()) {
        // Check the files argument and define the temp folder
        if ((is_array($files) && empty($files)) || $files == "") return false;
        $temp_directory = ROOT."/features/settings/temp/$filename/update/";

        // Define the files string as an array, if not, then loop through including all the files
        if (!is_array($files)) $files = array($files);
        foreach ($files as $file) {
            include path($temp_directory.$file);
        }
    }


    /**
     * Handles a manual update
     */
    public function manual_update() {
        // Get the update information
        $filename = filter_input(INPUT_POST, "filename");
        $update_information = $this->get_update_information($filename);

        // Get the system info
        $system_info = $this->get_system_info();

        // Extract the update files to the root directory
        $this->extract_update($filename, "root");

        // Run the update scripts based on what's requested
        if ($update_information['run_update_script'] == true) {
            // Include the update files, run the update function if it's there
            $this->include_update_files($filename, $update_information['update_files']);
            if (function_exists("update")) {
                if (!update($system_info)) {
                    throw new Exception("There was an error when running the update scripts.");
                }
            }
        }

        // Clean the temp folder and notify the user
        $this->clean_temp_folder();
        notify("admin", "success", "Everything went smoothly.  In order for things to take effect, you need to <a href='./'>refresh the page</a>.");
    }
}