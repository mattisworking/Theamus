<?php

/**
 * Updates from 0.2
 *
 * @return boolean
 */
function update_02($Theamus) {
    // Create the themes-data table
    $query = $Theamus->DB->custom_query("CREATE TABLE IF NOT EXISTS `".$Theamus->DB->system_table('themes-data')."` (`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), `key` TEXT NOT NULL, `value` TEXT NOT NULL, `selector` TEXT NOT NULL, `theme` VARCHAR(50) NOT NULL);");

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
function update_11($Theamus) {
    // Create the user sessions table
    $Theamus->DB->custom_query("CREATE TABLE IF NOT EXISTS `".$Theamus->DB->system_table('user_sessions')."` (`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), `key` TEXT NOT NULL, `value` TEXT NOT NULL, `ip_address` TEXT NOT NULL, `user_id` INT NOT NULL);");

    // Get the tables from the database
    $tables = array();
    $tables_query = $Theamus->DB->custom_query("SHOW TABLES");
    $results = $Theamus->DB->fetch_rows($tables_query);
    $all_tables = isset($results[0]) ? $results : $all_tables;
    
    foreach ($all_tables as $table) {
        $tables[] = $table;
    }

    // Rename the images table
    if (!in_array($Theamus->DB->system_table('media'), $tables));
    $Theamus->DB->custom_query("RENAME TABLE `".$Theamus->DB->system_table('images')."` TO `".$Theamus->DB->system_table('media')."`");

    // Find the session column in the user's table
    $users_table = $Theamus->DB->custom_query("SELECT `session` FROM `".$Theamus->DB->system_table('users')."` LIMIT 1");

    // Drop the session column
    if ($users_table) {
        $Theamus->DB->custom_query("ALTER TABLE `".$Theamus->DB->system_table('users')."` DROP COLUMN `session`;");
    }

    // Find the type column in the media table
    $media_table = $Theamus->DB->custom_query("SELECT `type` FROM `".$Theamus->DB->system_table('media')."` LIMIT 1");

    // Add the type column
    if (!$media_table) {
        $Theamus->DB->custom_query("ALTER TABLE `".$Theamus->DB->system_table('media')."` ADD `type` TEXT NOT NULL;");
    }

    return true;
}

function update_12($Theamus) {
    return true;
}

function update_version($Theamus, $update_information) {
    // Define the return array
    $return = array();

    // Update the version
    $return[] = $Theamus->DB->custom_query("UPDATE `".$Theamus->DB->system_table('settings')."` SET `version`='".$update_information['version']."'") ? true : false;

    // Disconnect from the database and return
    return in_array(false, $return) ? false : true;
}

function update_cleanup($Theamus) {
    // Remove the unnecessary folders
    $Theamus->Files->remove_folder($Theamus->file_path(ROOT."/themes/installer/"));
    $Theamus->Files->remove_folder($Theamus->file_path(ROOT."/features/install/"));
    $Theamus->Files->remove_folder($Theamus->file_path(ROOT."/update/"));
}