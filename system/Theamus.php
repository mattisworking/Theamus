<?php

/**
 * Theamus
 * PHP Version 5.5.3
 * Version 1.4.2
 * @package Theamus
 * @link http://www.theamus.com/
 * @author MMT
 */
class Theamus {
    /**
     * All of the Theamus system objects that will be usable to eachother
     */
    public $DB;
    public $Log;
    public $Call;
    public $User;
    public $Theme;
    public $Files;
    public $Pagination;
    public $Parsedown;
    protected $timer;

    public $version = '1.4.2';


    /**
     * Initialize all of the Theamus system classes
     *
     * @return
     */
    public function __construct() {
        $this->start_timer();

        $this->load_system_classes(); // Load the system classes to be initialized
        $this->load_external_sources(); // Load the external system classes

        $this->define_url(); // Define the base of the URL

        $this->DB           = new DB($this);            // Database access and management class

        // Get the system settings!
        if (!$this->DB->try_installer) $this->settings = $this->get_system_settings();

        $this->Log          = new Log($this);           // Error logging class
        $this->User         = new User($this);          // User control class
        $this->API          = new API($this);
        $this->Call         = new Call($this);          // Page call handling class
        $this->Theme        = new Theme($this);         // Theme handling class
        $this->Files        = new Files($this);         // File manipulation/control class
        $this->Pagination   = new Pagination($this);    // Pagination made ez class

        $this->Parsedown    = new ParsedownExtra();     // Parsedown text class

        return;
    }


    /**
     * Loads all of the Theamus required classes to be usable throughout the system
     *
     * @throws Exception
     */
    protected function load_system_classes() {
        // Define all of the system required files that need to be included in order
        // for Theamus to work properly
        $system_class_files = array(
            'db.class.php',
            'log.class.php',
            'api.class.php',
            'call.class.php',
            'user.class.php',
            'theme.class.php',
            'files.class.php',
            'pagination.class.php',
            'instance.class.php'
        );

        // Loop through each of the system files
        foreach ($system_class_files as $file) {
            // Define the COMPLETE PATH to the file
            $file_path = $this->file_path(ROOT.'/system/'.$file);

            // Check for the file's existence and require it if it does
            if (file_exists($file_path)) {
                require_once $file_path;

            // Throw an exception to the index handler showing the issue on the page.
            } else {
                throw new Exception('Failed to this load page.<br>The <strong>'.$file.'</strong> system file is missing.');
            }
        }
    }


    /**
     * Loads files that were written by someone else in the world but is used in Theamus
     *
     * @throws Exception
     */
    protected function load_external_sources() {
        // Define all of the files that were created by someone else and is required
        // for Theamus to work properly
        $external_files = array(
            'phpmailer/class.phpmailer.php',
            'parsedown/Parsedown.php',
            'parsedown/ParsedownExtra.php',
            'jsmin/jsmin.php'
        );

        // Loop through all of the external files
        foreach ($external_files as $file) {
            // Define the COMPLETE PATH to the file
            $file_path = $this->file_path(ROOT.'/system/external/'.$file);

            // Check for the file's existence and require it if it does
            if (file_exists($file_path)) {
                require_once $file_path;

            // Throw an exception to the index handler showing the issue on the page.
            } else {
                throw new Exception('Failed to this load page.<br>The <strong>'.$file.'</strong> external system file is missing.');
            }
        }
    }


    /**
     * This function will configure paths to be acceptable on both Windows and *nix based machines.
     *
     * @param string $path
     * @return string $path
     */
    public function file_path($path) {
        // Check to see if Theamus is installed on a Windows machine and flip all of the slashes
        if (strpos($path, ":\\") !== false) $path = str_replace("/", "\\", $path);

        return $path; // Return the path with or without the fixes
    }


    /**
     * This function will configure paths to be readable to web browsers
     *
     * @param string $path
     * @return string
     */
    public function web_path($path) {
        // Check to see if the path is a Windows path and flip the slashes
        if (strpos($path, "\\") !== false) $path = str_replace("\\", "/", $path);

        return $path; // Return the path with or without the fixes
    }


    /**
     * Gets the Theamus settings from the database
     *
     * @return array
     * @throws Exception
     */
    public function get_system_settings() {
        // Query the database for Theamus settings
        $query = $this->DB->select_from_table(
            $this->DB->system_table('settings'),
            array());

        // Check the query
        if (!$query) throw new Exception('Failed to get the system settings from the database.');

        // Count the rows returned
        if ($this->DB->count_rows($query) != 1) throw new Exception('There\'s something wrong with the system settings.');

        // Fetch and return the settings
        return $this->DB->fetch_rows($query);
    }


    /**
     * Turns an ugly old array into something shiny for your eye rounds
     *
     * @param array $array
     * @param boolean $return
     * @return strong
     */
    public function pre($array, $return = false) {
        // Define the string to return
        $ret[] = '<pre>';
        $ret[] = print_r($array, true);
        $ret[] = '</pre>';

        // Return or echo the pretty string/array
        if ($return == true) return implode('', $ret);
        else echo implode('', $ret);
    }


    /**
    * Shortcut to email people through the provided database information (and SMTP)
    *
    * @param string $to
    * @param string $subject
    * @param string $message
    * @return boolean
    */
   public function mail($to, $subject, $message) {
       $settings   = $this->settings;

       // Define all of the email information using PHPMailer
       $mail = new PHPMailer();
       $mail->IsSMTP();
       $mail->SMTPAuth   = true;
       $mail->SMTPSecure = $settings['email_protocol'];
       $mail->Host       = $settings['email_host'];
       $mail->Port       = $settings['email_port'];
       $mail->Username   = $settings['email_user'];
       $mail->Password   = $this->decrypt_string($settings['email_password']);
       $mail->From       = $settings['email_user'];
       $mail->FromName   = $settings['name'];

       $mail->IsHTML(true);
       $mail->Subject = $subject;
       $mail->Body = $message;

       $mail->AddAddress($to);

       // Send the email out
       return $mail->Send();
   }


   /**
    * Takes the user back a page
    *
    * @return header
    */
   public function back_up() {
       // Define the URL to go to
       $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
       $url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

       // If the previous URL isn't the one the user is at currently (avoid loops)
       if ($url != $this->base_url) {
           // Relocate!
           substr($url, -1) != '/' ? header('Location: '.$url.'/') : header('Location: ../');
       }
   }


   /**
    * Prints out an alert on the website
    *
    * @param string $for
    * @param string $type
    * @param string $message
    * @param string $extras
    * @return boolean
    */
    public function notify($type = 'success', $message = '', $return = false) {
        // Define the icon to use
        $glyph = array(
            'success' => 'ion-checkmark-round',
            'danger'  => 'ion-close',
            'warning' => 'ion-alert',
            'info'    => 'ion-information',
            'spinner' => 'spinner spinner-fixed-size'
        );

        // Define the actual notifictaion
        $ret = '<div class="alert alert-'.$type.'" id="notify">';
        $ret .= '<span class="glyphicon '.$glyph[$type].'"></span>'.$message.'</div>';

        // Echo or return depending on the specifications from the user
        if ($return == false) echo $ret;
        else return $ret;
    }


    /**
     * Defines the URL that was used to make the current call
     *
     * @return string
     */
    private function define_url() {
        $protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
        $domain = $_SERVER['HTTP_HOST'];
        $directory = dirname($_SERVER['PHP_SELF']) . "/";

        $this->base_url = trim($protocol.$domain.$directory, "/")."/";
    }


    /**
     * Prints out an input that requests the site to include an extra javascript file
     *
     * @param string $path
     * @return boolean
     */
    public function load_js_file($path, $force = "false") {
       echo "<input type='hidden' name='addscript' data-force='{$force}' value='".$path."?x=".time()."' />";
       return true;
    }


    /**
     * Sets the timer to be now, every time Theamus loads
     */
    private function start_timer() {
        $this->timer = microtime(true);
    }


    /**
     * Returns the time inbetween this function call and the start timer time
     *
     * @return number
     */
    public function get_run_time() {
        return microtime(true) - $this->timer;
    }
    
    
    /**
     * Returns an encrypted & utf8-encoded
     * 
     * @return string $encrypted_string
     */
    function encrypt_string($pure_string) {
        $encryption_key = $this->DB->get_config_salt("password");
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
        return $encrypted_string;
    }

    
    /**
     * Returns decrypted original string
     * 
     * @return string $decrypted_string
     */
    function decrypt_string($encrypted_string) {
        $encryption_key = $this->DB->get_config_salt("password");
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return trim($decrypted_string);
    }
    
    
    /**
     * Finds the server specified memory limit and turns it from human readable to bytes.
     * 
     * Thanks you to Muhammad Alvin for this answer. http://stackoverflow.com/a/10209530/3777524
     * You helped me!
     * 
     * @return number
     */
    function get_server_memory_limit() {
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            if ($matches[2] == 'M') {
                $memory_limit = $matches[1] * 1024 * 1024;
            } else if ($matches[2] == 'K') {
                $memory_limit = $matches[1] * 1024;
            }
        }
        return $memory_limit;
    }
}