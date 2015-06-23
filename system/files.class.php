<?php

/**
 * Files - Theamus file access/manipulation class
 * PHP Version 5.5.3
 * Version 1.4.1
 * @package Theamus
 * @link http://www.theamus.com/
 * @author MMT (helllomatt) <mmt@itsfake.com>
 */
class Files {
    /**
     * Constructs the class, just returns true
     *
     * @return boolean
     */
    public function __construct($t) {
        $this->Theamus = $t; // Make other Theamus classes usable
        return true;
    }


    /**
     * Scans a directory for all of the files and folders inside and returns a
     *  flattened/cleaned array of all the file or folder names
     *
     * @param string $path
     * @param string $clean
     * @param string $return_type
     * @return array $ret
     */
    function scan_folder($path, $clean = "", $return_type = "files", $root_only = false) {
        $ret = array();

        // Scan the define folder for contents
        $root = scandir($this->Theamus->file_path($path));

        // Loop through the contents
        foreach ($root as $value) {
            // Define the complete formatted current path of the loop
            $current_path = $this->Theamus->file_path("{$path}/{$value}");

            // Skip up a level files
            if ($value === '.' || $value === '..') continue;

            // If the current file is a folder and we're looking to return folders only
            if (is_dir($current_path) && ($return_type == "folders" || $return_type == "all")) {
                $ret[] = $current_path; // Add the folder to the return array

                // Recurse into the folder, adding the information to the return array as well
                if (!$root_only) {
                    foreach ($this->scan_folder($current_path, $clean, $return_type) as $value) $ret[] = $value;
                }
                continue;

            // If the current file is a file and we're looking to return files only
            } elseif (is_file($current_path) && ($return_type == "files" || $return_type == "all")) {
                $ret[] = $current_path; // Add the file to the return array
                continue;
            }

            // Only if we're returning files recurse into folders, if it's done without
            // the condition, it will try to recurse into files as well
            if ($return_type == "files" && !$root_only) {
                foreach ($this->scan_folder($current_path, $clean, $return_type) as $value) $ret[] = $value;
            }
        }

        // If there is a need to remove some leading file path off of each value, do so
        if ($clean != "") $ret = $this->clean_filenames($ret, $clean);

        return $ret; // Return the information
    }


    /**
     * Goes through all of the file names provided by scan_folder() and strips
     *  out any unwanted information.
     *
     * e.g. /var/www/theamus/file.php -> file.php
     *
     * @param array $array
     * @param string $clean
     * @return boolean|array $result
     */
    function clean_filenames($array, $clean = '') {
        if (is_array($array)) {
            $result = array();
            foreach ($array as $val) $result[] = str_replace($this->Theamus->file_path($clean . '/'), '', $val);
            return $result;
        }
        return false;
    }


    /**
     * Recursively removes a folder and all of the contents inside of it
     *
     * @param string $path
     * @return boolean
     */
    public function remove_folder($path = "") {
        $dir = $this->Theamus->file_path($path);
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == "." || $item == "..") continue;
            if (!$this->remove_folder($dir."/".$item)) {
                if (!$this->remove_folder($dir."/".$item)) return false;
            }
        }
        return rmdir($dir);
    }


    /**
     * Extracts a zip file
     *
     * @param string $f
     * @param string $d
     * @return boolean
     */
    public function extract_zip($f, $d) {
        $z = new ZipArchive();
        if ($z->open($f) === true) {
            $z->extractTo($d);
            $z->close();
            return true;
        }
        return false;
    }
}