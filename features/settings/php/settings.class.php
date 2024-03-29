<?php

class Settings {
    protected $update_server = "http://theamus.com/releases";
    protected $update_server_path = "http://theamus.com/features/releases/packages";


    /**
     * Connects to Theamus
     *
     * @param object $t
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;

        // Check for administrators only
        if (!$this->Theamus->User->is_admin()) die('Only administrators can do that.');

        return;
    }


    /**
     * Get the home information from the database
     *
     * @return string
     */
    private function get_system_home() {
        return $this->Theamus->settings['home'];
    }


    /**
     * Decode the homepage information found in the database
     *
     * @return string
     * @throws Exception
     */
    public function decode_home() {
        // Get and define the homepage
        $home = $this->Theamus->DB->t_decode($this->get_system_home());

        // Check the homepage for validity
        if ($home[0] != "homepage") throw new Exception("Invalid home page information.");
        else return $home;
    }


    /**
     * Gets information from the database for a specific table with or without
     * a specific ID to look for
     *
     * @param string $table
     * @param int $id
     * @return array
     * @throws Exception
     */
    private function get_db_rows($table = '', $id = 0) {
        // Check the table name
        if ($table == '') throw new Exception('Invalid table name.');

        // Check the ID
        if (!is_numeric($id)) throw new Exception('Invalid ID to look for.');

        // Define the clause information for the ID
        $clause = $id == 0 ? array() : array('operator' => '', 'conditions' => array('id' => $id));

        // Query the database looking for the desired information
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table($table),
                array(),
                $clause);

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to find '.$table.'.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find any '.$table.'.');

        // Return the information from the query
        return $this->Theamus->DB->fetch_rows($query);
    }


    /**
     * Gets all of the pages from the database and returns them as options for
     * a select element
     *
     * @param array $home
     * @return string
     */
    public function get_pages_select($home = array()) {
        // Define the page ID of the current homepage setup (if applicable)
        $page_id = $home['type'] == 'page' ? $home['id'] : 0;

        // Get information about all of the Theamus pages
        $pages = $this->get_db_rows('pages');

        $return = array(); // Initialize the return array

        // Loop through all of the pages
        foreach (isset($pages[0]) ? $pages : array($pages) as $page) {
            $selected = $page['id'] == $page_id ? 'selected' : '';
            $return[] = '<option value="'.$page['id'].'" '.$selected.'>'.$page['title'].'</option>';
        }

        // Return all of the options as a string
        return implode('', $return);
    }


    /**
     * Gets all of the features from the database and returns them as options
     * for a select element
     *
     * @param array $home
     * @return string
     */
    public function get_features_select($home = array()) {
        // Defien the feature ID of the current homepage setup
        $feature_id = $home['type'] == 'feature' ? $home['id'] : 0;

        // Get all of the feature information from the database
        $features = $this->get_db_rows('features');

        $return = array(); // Initialize the return array

        // Loop through all of the features
        foreach (isset($features[0]) ? $features : array($features) as $feature) {
            $selected = $feature['id'] == $feature_id ? 'selected' : '';
            $return[] = '<option value="'.$feature['id'].'" '.$selected.'>'.$feature['name'].'</option>';
        }

        // Return the options as a string
        return implode('', $return);
    }


    /**
     * Gets all of the view files for a feature and returns them as options for
     * a select element
     *
     * @param array $args
     * @return string
     */
    public function get_feature_files_select($args) {
        // Define the home information from the database
        $home = $this->decode_home();

        // Check for a feature id in the arguments
        if (!isset($args['feature']) || $args['feature'] == '' || !is_numeric($args['feature'])) {
            throw new Exception('Invalid feature ID.');
        }

        // Get all of the information about the feature in question
        $feature = $this->get_db_rows("features", $args['feature']);

        // Define the path to the feature that's looking for files
        $feature_path = $this->Theamus->file_path(ROOT.'/features/'.$feature['alias'].'/views');

        // Get all of the view files from the feature
        $files = $this->Theamus->Files->scan_folder($feature_path, $feature_path);

        // Check for no view files
        if (count($files) == 0) $return[] = '<option>There are no view files for this feature.</option>';

        // Loop through all of the files
        foreach ($files as $file) {
            // Clean up the file name
            $clean_name = ucwords(
                    str_replace('.php', '',
                    str_replace('/', ' / ',
                    str_replace('\\', ' / ',
                    str_replace('_', ' ',
                    str_replace('-', ' ', $file))))));

            $selected = ''; // Initialize the option selected variable

            // Check if this file is the one that is the homepage right now
            if (array_key_exists('file', $home) && $home['file'] != '') $selected = $home['file'].'.php' == $file ? 'selected' : '';

            // Define the default selected option
            elseif ($file == 'index.php') $selected = 'selected';

            // Show the feature file as an option
            $return[] = '<option value="'.str_replace('.php', '', $file).'" '.$selected.'>'.$clean_name.'</option>';
        }

        return implode('', $return); // Return the feature file options
    }


    /**
     * Gets the name of the website
     *
     * @return string
     */
    public function get_site_name() {
        return $this->Theamus->settings['name'];
    }


    /**
     * Defines homepage information for sessions (before/after)
     *
     * @param array $home
     * @param string $ba
     * @return string
     */
    public function get_session_value($home, $ba) {
        // Check for the array values before anything
        if (!isset($home[$ba.'-type'])) return '';

        // Initialize the return array
        $return = array($ba.'-type=\"'.$home[$ba.'-type'].'\";');

        // Define the page id
        if ($home[$ba.'-type'] == 'page') $return[] = $ba.'-id=\"'.$home[$ba.'-id'].'\";';

        // Define the feature id and file
        if ($home[$ba.'-type'] == 'feature') {
            $return[] = $ba.'-id=\"'.$home[$ba.'-id'].'\";';
            $return[] = $ba.'-file=\"'.$home[$ba.'-file'].'\";';
        }

        // Define the URL
        if ($home[$ba.'-type'] == 'url') $return[] = $ba.'-id=\"'.$home[$ba.'-url'].'\";';

        // Return the information as a string
        return implode('', $return);
    }


    /**
     * Saves customization settings
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function save_customization($args) {
        // Check for a site name
        if (!isset($args['name']) || $args['name'] == '') throw new Exception('Invalid Site Name.');

        // Check for a home page
        if (!isset($args['home-page']) || $args['home-page'] == '') throw new Exception('Invalid home page.');

        // Make the query to save this information to the database
        $query = $this->Theamus->DB->update_table_row(
                $this->Theamus->DB->system_table('settings'),
                array('name' => $args['name'],
                    'home'   => $args['home-page']));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to save information.');
        }

        return true; // Return true!
    }


    /**
     * Saves the system settings to the database
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function save_settings($args) {
        $query_data = array(); // Initialize the query data array

        // Check for the config-email variable
        if (!isset($args['config-email'])) throw new Exception('Invalid config email.');
        
        if (!isset($args['settings_log-categories'])) $args['settings_log-categories'] = "";
        $query_data['logging'] = $args['settings_log-categories'];

        // Check for the email setup to be changed
        if ($args['config-email'] !== 'false') {
            // Add this information to the query data
            $query_data['email_host'] = $args['host'];
            $query_data['email_protocol'] = $args['protocol'];
            $query_data['email_port'] = $args['port'];
            $query_data['email_user'] = $args['email'];
            $query_data['email_password'] = $this->Theamus->encrypt_string($args['password']);
        }
        
        // Save the page information
        $query_data['show_page_information'] = $args['page_information'] == "" ? "" : json_encode(explode(",", $args['page_information']));

        // Check for display errors variable
        if (!isset($args['errors'])) throw new Exception('Invalid developer errors value.');

        // Add the display errors value to the query data
        $query_data['display_errors'] = $args['errors'] !== 'false' ? 1 : 0;

        // Query the database, updating it with this information
        $query = $this->Theamus->DB->update_table_row(
                $this->Theamus->DB->system_table('settings'),
                $query_data);

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to save this information.');
        }

        return true; // Return true!
    }


    /**
     * Cleans all of the contents out of the temp directory
     */
    protected function clean_temp_folder() {
        // Define the path to the temp directory and get the files/folders from it
        $temp_directory = ROOT."/features/settings/temp";
        $temp_files     = $this->Theamus->Files->scan_folder($temp_directory);
        $temp_folders   = $this->Theamus->Files->scan_folder($temp_directory, false, "folders");

        // Loop through all of the files and folders, removing them
        foreach ($temp_files as $file) if ($file != $this->Theamus->file_path($temp_directory."/blank.txt")) unlink($file);
        foreach ($temp_folders as $folder) $this->Theamus->Files->remove_folder($folder);
    }
    
    
    /**
     * Gets update information from GitHub
     * 
     * @return array
     */
    public function get_update_info() {
        // Repo Releases URL to GitHub API
        $url = "https://api.github.com/repos/helllomatt/Theamus/releases";
        
        // Get releases information
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, "helllomatt-Theamus");
        $api_data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        $releases = array();
        foreach ($api_data as $release) $releases[] = $release['tag_name'];

        // Download the latest release
        $needs_update = 0;
        $update_path = "";
        
        if ($this->Theamus->version != $api_data[0]['tag_name']) {
            $needs_update = 1;
            $update_path = "https://github.com/helllomatt/Theamus/archive/{$releases[0]}.zip";
        }
        
        return array(
            "currentVersion" => $this->Theamus->version,
            "releasedVersions" => $releases,
            "needsUpdate" => $needs_update,
            "updatePath" => $update_path,
            "updateVersion" => $releases[0],
            "updateNotes" => $this->Theamus->Parsedown->text($api_data[0]['body'])
        );
    }


    /**
     * Downloads the latest version of Theamus
     *
     * @return string
     * @throws Exception
     */
    protected function download_update($update_path) {
        $temp_filename = md5(time());
        $temp_file = $this->Theamus->file_path(ROOT."/features/settings/temp/{$temp_filename}");

        $server_file = @file_get_contents($update_path);
        
        if (!$server_file) {
            $this->Theamus->Log->system("Failed to download update file: {$update_path}");
            throw new Exception("Failed to download the update because it doesn't exist!");
        } else {
            $downloaded = 1;
        }
        
        $fp = fopen($this->Theamus->file_path($temp_file.".zip"), "w");
        if (!$fp) {
            $this->Theamus->Log->system("Failed to write file {$temp_file}.zip. Check file permissions.");
            throw new Exception("Failed to download the latest update because of file permssions.");
        } else {
            fwrite($fp, file_get_contents($update_path));
            fclose($fp);
            chmod($this->Theamus->file_path($temp_file.".zip"), 0777);
        }
        
        return array(
            "downloaded" => $downloaded,
            "foldername" => $temp_file,
            "filename" => $temp_filename,
            "filePath" => $temp_file
        );
    }

    
    /**
     * Automatically updates Theamus from GitHub
     * 
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function auto_update($args = array()) {
        $download = $this->download_update($args['updatePath']);
        $this->extract_update($download['filename']);
        
        $this->copy_files($download['filePath'], $args['updateVersion']);
        $this->rezip($download['filePath']); // Perform checks to ensure this is a legit update
        
        $update_information = $this->get_update_information($download['filename']);
        $check_information = $this->check_update_information($update_information);
        if ($check_information) $this->update_information = $this->define_update_information($update_information, $download['filename']);

        // Extract the update files to the root directory
        $this->extract_update($download['filename'], "root");

        // Run the update scripts based on what's requested
        if ($update_information['run_update_script'] == true) {
            // Include the update files, run the update function if it's there
            $this->include_update_files($download['filename'], $update_information['update_files']);
            if (function_exists("update")) {
                if (!update($this->Theamus, $update_information)) {
                    throw new Exception("There was an error when running the update scripts.");
                }
            }
        }

        $this->clean_temp_folder();

        return true;
    }
    
    
    /**
     * Github packages things differently, so we need to get everything into a root folder
     * for the updater and then for a rezip to be overlaied on everything rewriting files.
     * 
     * I think this might be an extra step.
     * 
     * @param string $file_path
     * @param string $version
     */
    private function copy_files($file_path, $version) {
        $files = $this->Theamus->Files->scan_folder($file_path);
        
        foreach ($files as $file) {
            $file_name = str_replace("Theamus-{$version}/", "", $file);
            if (!is_dir(dirname($file_name))) mkdir(dirname($file_name), 0777, true);
            rename($file, $file_name);
        }
        
        $this->Theamus->Files->remove_folder($file_path."/Theamus-{$version}/");
        unlink($this->Theamus->file_path($file_path.".zip"));
    }
    
    
    /**
     * Rezip files from the GitHub version to something easier for me.
     * 
     * @param string $file_path
     * @throws Exception
     */
    private function rezip($file_path) {
        $files = $this->Theamus->Files->scan_folder($file_path);
        $zip = new ZipArchive();
        
        if ($zip->open($file_path.".zip", ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file, str_replace($file_path."/", "", $file));
            }
            $zip->close();
        } else {
            $this->Theamus->Log->system("Failed to create the zip filed for the update. Check your file permissions.");
            throw new Exception("Failed to restructure the update.");
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
        if (move_uploaded_file($file['tmp_name'], $this->Theamus->file_path($temp_directory.$temp_filename.".zip"))) {
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
        if (!$this->Theamus->Files->extract_zip($this->Theamus->file_path($temp_directory.$filename.".zip"), $this->Theamus->file_path($extract_directory))) {
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
        $information_file = $this->Theamus->file_path($temp_directory."update/update.json");

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
            include $this->Theamus->file_path($temp_directory.$file);
        }
    }


    /**
     * Handles a manual update
     */
    public function manual_update() {
        // Upload and extract the file
        $uploaded_file = $this->get_uploaded_file();
        $uploaded_filename = $this->upload_file($uploaded_file);
        $this->extract_update($uploaded_filename);

        // Get the update information
        $update_information = $this->get_update_information($uploaded_filename);

        // Extract the update files to the root directory
        $this->extract_update($uploaded_filename, "root");

        // Run the update scripts based on what's requested
        if ($update_information['run_update_script'] == true) {
            // Include the update files, run the update function if it's there
            $this->include_update_files($uploaded_filename, $update_information['update_files']);
            if (function_exists("update")) {
                if (!update($this->Theamus, $update_information)) {
                    throw new Exception("There was an error when running the update scripts.");
                }
            }
        }

        // Clean the temp folder and notify the user
        $this->clean_temp_folder();
        return true;
    }


    /**
     * Define the settings tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function settings_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('Settings', 'settings/settings.php', 'Theamus Settings'),
            array('Customization', 'settings/index.php', 'Site Customization'),
            array('Manual Update', 'settings/update-manually.php', 'Manual Update'),
            array('More', array(
                array('View Logs', 'settings/logs/view.php', 'View Logs')
            ), 'right'));

        // Return the HTML tabs
        return $this->Theamus->Theme->generate_admin_tabs("settings-tab", $tabs, $file);
    }
    
    
    /**
     * Sends a test email to whoever in order to check the configuration settings
     * 
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function test_email($args = array()) {
        if (!isset($args['protocol']) || $args['protocol'] == "") {
            throw new Exception("Invalid protocol defined.");
        }
        
        if (!isset($args['host']) || $args['host'] == "") {
            throw new Exception("Invalid email host defined.");
        }
        
        if (!isset($args['port']) || !is_numeric($args['port'])) {
            throw new Exception("Invalid port defined.");
        }
        
        if (!isset($args['email']) || $args['email'] == "") {
            throw new Exception("Invalid email username defined.");
        }
        
        if (!isset($args['to']) || $args['to'] == "" || !filter_var($args['to'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid 'to' email address.");
        }
        
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        
        try {
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = $args['protocol'];
            $mail->Host       = $args['host'];
            $mail->Port       = $args['port'];
            $mail->Username   = $args['email'];
            $mail->Password   = $args['password'];
            $mail->From       = $args['email'];
            $mail->FromName   = $args['email'];

            $mail->Subject = "test email, please ignore.";
            $mail->Body = "helllo.  this is a test email.  if you got it, then your email setup works!";

            $mail->AddAddress($args['to']);

            // Send the email out
            return $mail->Send();
        } catch (phpmailerException $e) {
            throw new Exception($e->errorMessage());
        }
    }
}