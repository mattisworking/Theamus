<?php

/**
 * Files - Theamus file access/manipulation class
 * PHP Version 5.5.3
 * Version 1.4.0
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Matt Temet
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
     * @return type array
     */
    function scan_folder($path, $clean = false, $return_type = "files") {
        $ret = array();
        $root = scandir($this->Theamus->file_path($path));
        foreach ($root as $value) {
            if ($value === '.' || $value === '..') continue;
            if (is_file($this->Theamus->file_path($path."/".$value)) && $return_type == "files") {
                $ret[] = $this->Theamus->file_path($path."/".$value);
                continue;
            } elseif (is_dir($this->Theamus->file_path($path."/".$value)) && $return_type == "folders") {
                $ret[] = $this->Theamus->file_path($path."/".$value);
                continue;
            }

            if (is_dir($this->Theamus->file_path($path."/".$value))) foreach ($this->scan_folder($path."/".$value) as $value) $ret[] = $value;
        }
        if ($clean != false) $ret = $this->clean_filenames($ret, $clean);
        return $ret;
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