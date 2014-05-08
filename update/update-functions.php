<?php

/**
 * Updates from 0.2
 * 
 * @return boolean
 */
function update_02() {
    // // Connect to the database
    $tData      = new tData();
    $tData->db  = $tData->connect(true);

    // Create the themes-data table
    $query = $tData->db->query("CREATE TABLE IF NOT EXISTS `".$tData->get_system_prefix()."_themes-data` (`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), `key` TEXT NOT NULL, `value` TEXT NOT NULL, `selector` TEXT NOT NULL, `theme` VARCHAR(50) NOT NULL);");

    // Check the query and return
    if ($query == false) {
        return false;
    }
    return true;
}

/**
 * Updates to 1.1
 * 
 * @return boolean
 */
function update_11() {
    // Connect to the database
    $tData      = new tData();
    $tData->db  = $tData->connect();
    $prefix     = $tData->get_system_prefix();

    // Create the user sessions table
    $tData->db->query("CREATE TABLE IF NOT EXISTS `".$prefix."_user-sessions` (`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), `key` TEXT NOT NULL, `value` TEXT NOT NULL, `ip_address` TEXT NOT NULL, `user_id` INT NOT NULL);");
    
    // Get the tables from the database
    $tables = array();
    $tables_query = $tData->db->query("SHOW TABLES");
    while ($row = $tables_query->fetch_array()) {
        $tables[] = $row[0];
    }
    
    // Rename the images table
    if (!in_array($prefix."_media", $tables));
    $tData->db->query("RENAME TABLE `".$prefix."_images` TO `".$prefix."_media`");
    
    // Find the session column in the user's table
    $users_table = $tData->db->query("SELECT `session` FROM `".$prefix."_users` LIMIT 1");
    
    // Drop the session column
    if ($users_table) {
        $tData->db->query("ALTER TABLE `".$prefix."_users` DROP COLUMN `session`;");
    }
    
    // Find the type column in the media table
    $media_table = $tData->db->query("SELECT `type` FROM `".$prefix."_media` LIMIT 1");
    
    // Add the type column
    if (!$media_table) {
        $tData->db->query("ALTER TABLE `".$prefix."_media` ADD `type` TEXT NOT NULL;");
    }
    
    return true;
}

function update_version($version) {
    // Define the return array, connect and define database variables
    $return = array();
    $tDataClass = new tData();
    $tData = $tDataClass->connect();
    $prefix = $tDataClass->get_system_prefix();

    // Update the version
    $return[] = $tData->query("UPDATE `".$prefix."_settings` SET `version`='$version'") ? true : false;

    // Disconnect from the database and return
    $tDataClass->disconnect();
    return in_array(false, $return) ? false : true;
}

function update_cleanup() {
    // Define the file management class
    $tFiles = new tFiles();

    // Remove the unnecessary folders
    $tFiles->remove_folder(path(ROOT."/themes/installer/"));
    $tFiles->remove_folder(path(ROOT."/features/install/"));
    $tFiles->remove_folder(path(ROOT."/update/"));
}