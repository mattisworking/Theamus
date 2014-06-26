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

function update_12() {
    if (!update_users_table()) return false;
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

function update_users_table() {
    $old_table_name = $this->tData->prefix.'_users';
    $temp_table_name = $this->tData->prefix.'_users-new';

    $this->tData->db->beginTransaction();

    $create_table = $this->tData->custom_query('CREATE TABLE IF NOT EXISTS `'.$temp_table_name.'` (`id` int(11) NOT NULL AUTO_INCREMENT, `key` varchar(100) NOT NULL, `value` TEXT NOT NULL, `selector` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;');
    if ($create_table === false) {
        $this->tData->db->rollBack();
        return false;
    }

    $all_accounts = $this->get_accounts();
    $existing_accounts = isset($all_accounts[0]) ? $all_accounts : array($all_accounts);

    $query_data = array();

    foreach ($existing_accounts as $ea) {
        foreach ($ea as $key => $value) {
            $query_data[] = array("key" => $key, "value" => $value, "selector" => $ea['id']);
        }
    }

    $add_new_data = $this->tData->insert_table_row($temp_table_name, $query_data);

    if ($add_new_data == false) {
        $this->tData->db->rollBack();
        return false;
    }

    $remove_old_table = $this->tData->custom_query('DROP TABLE `'.$old_table_name.'`');

    if ($remove_old_table == false) {
        $this->tData->db->rollBack();
        return false;
    }

    $rename_temp_table = $this->tData->custom_query('RENAME TABLE `'.$temp_table_name.'` TO `'.$old_table_name.'`');

    if ($rename_temp_table == false) {
        $this->tData->db->rollBack();
        return false;
    }

    $this->tData->db->commit();
    return true;
}