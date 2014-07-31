<?php

/**
 * Theamus
 * PHP Version 5.5.3
 * Version 1.3
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Eyrah Temet (Eyraahh) <info@theamus.com>
 */

class Theamus {
    /**
     * All of the Theamus system objects that will be usable to eachother
     */
    public $DB;
    public $Call;
    public $User;
    public $Theme;
    public $Files;
    public $Pagination;


    /**
     * Initialize all of the Theamus system classes
     *
     * @return
     */
    public function __construct() {
        $this->load_system_classes(); // Load the system classes to be initialized

        $this->DB           = new DB($this);            // Database access and management class

        $this->settings = $this->get_system_settings(); // Get the system settings!

        $this->User         = new User($this);          // User control class
        $this->API          = new API($this);
        $this->Call         = new Call($this);          // Page call handling class
        $this->Theme        = new Theme($this);         // Theme handling class
        $this->Files        = new Files($this);         // File manipulation/control class
        $this->Pagination   = new Pagination($this);    // Pagination made ez class

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
            'api.class.php',
            'call.class.php',
            'user.class.php',
            'theme.class.php',
            'files.class.php',
            'pagination.class.php'
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
       $mail->Password   = $settings['email_password'];
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
   function back_up() {
       // Define the URL to go to
       $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
       $url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

       // If the previous URL isn't the one the user is at currently (avoid loops)
       if ($url != base_url) {
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
   function notify($type = 'success', $message = '', $return = false) {
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
}