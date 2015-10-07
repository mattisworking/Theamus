<?php

/**
 * Updates the version of Theamus in the database
 *
 * @param string $version
 * @return boolean
 */
function update_version($version, $Theamus = '') {
    if ($Theamus != '') {
        if (!$Theamus->DB->update_table_row(
            $Theamus->DB->system_table('settings'),
            array('version' => $version))) return false;
    } else {
        // Connect to the DB
        $tData = new tData();
        $tData->db = $tData->connect();
        $prefix = $tData->get_system_prefix().'_';

        // Update the version
        if (!$tData->db->query("UPDATE `".$prefix."settings` SET `version`='$version'")) return false;
    }

    return true;
}


/**
 * Removes installer files
 */
function update_cleanup($Theamus = '') {
    if ($Theamus != '') {
        $Theamus->Files->remove_folder($Theamus->file_path(ROOT."/themes/installer/"));
        $Theamus->Files->remove_folder($Theamus->file_path(ROOT."/features/install/"));
        $Theamus->Files->remove_folder($Theamus->file_path(ROOT."/update/"));
    } else {
        $files = new tFiles();
        $files->remove_folder(path(ROOT."/themes/installer/"));
        $files->remove_folder(path(ROOT."/features/install/"));
        $files->remove_folder(path(ROOT."/update/"));
    }
}


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
    $prefix     = $tData->get_system_prefix().'_';

    // Create the user sessions table
    $tData->db->query("CREATE TABLE IF NOT EXISTS `".$prefix."user-sessions` (`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), `key` TEXT NOT NULL, `value` TEXT NOT NULL, `ip_address` TEXT NOT NULL, `user_id` INT NOT NULL);");

    // Get the tables from the database
    $tables = array();
    $tables_query = $tData->db->query("SHOW TABLES");
    while ($row = $tables_query->fetch_array()) {
        $tables[] = $row[0];
    }

    // Rename the images table
    if (!in_array($prefix."media", $tables));
    $tData->db->query("RENAME TABLE `".$prefix."images` TO `".$prefix."media`");

    // Find the session column in the user's table
    $users_table = $tData->db->query("SELECT `session` FROM `".$prefix."users` LIMIT 1");

    // Drop the session column
    if ($users_table) {
        $tData->db->query("ALTER TABLE `".$prefix."users` DROP COLUMN `session`;");
    }

    // Find the type column in the media table
    $media_table = $tData->db->query("SELECT `type` FROM `".$prefix."media` LIMIT 1");

    // Add the type column
    if (!$media_table) {
        $tData->db->query("ALTER TABLE `".$prefix."media` ADD `type` TEXT NOT NULL;");
    }

    return true;
}


/**
 * Updates to 1.2
 *
 * @return boolean
 */
function update_12() {
    return true;
}


/**
 * Updates to 1.3.0
 *
 * @return boolean
 */
function update_130() {
    // Connect to the DB class
    $tData = new tData();
    $tData->db = $tData->connect();
    $prefix = $tData->get_system_prefix().'_';

    // Do a check to see if this function has run already
    $check_query = $tData->db->query('SELECT `session_key` FROM `'.$prefix.'user-sessions`');
    if ($check_query) return true;

    // Alter the settings table
    if (!$tData->db->query('ALTER TABLE `'.$prefix.'settings` MODIFY `home` TEXT;')) return false;

    // Add the log table
    if (!$tData->db->query('CREATE TABLE IF NOT EXISTS `'.$prefix.'logs` (`id` int(11) NOT NULL AUTO_INCREMENT, `message` text NOT NULL, `class` varchar(100) NOT NULL, `function` varchar(150) NOT NULL, `line` int(11) NOT NULL, `file` varchar(500) NOT NULL, `type` varchar(50) NOT NULL, `time` datetime NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;')) return false;

    // Change the content column in the pages table
    if (!$tData->db->query('ALTER TABLE `'.$prefix.'pages` CHANGE `content` `raw_content` TEXT')) return false;

    // Add the logging column to the settings table
    if (!$tData->db->query('ALTER TABLE `'.$prefix.'settings` ADD `logging` TEXT NOT NULL after `version`')) return false;
    
    // Add the favicon column to the settings table
    if (!$tData->db->query('ALTER TABLE `'.$prefix.'settings` ADD `favicon_path` VARCHAR(200) NOT NULL after `logging`')) return false;

    // Trim the 'create-groups' permission
    if (!$tData->db->query('UPDATE `'.$prefix.'permissions` SET `permission` = "create_groups" WHERE `permission` LIKE "%create_groups"')) return false;

    // Update the user sessions table
    if (!$tData->db->multi_query('DROP TABLE IF EXISTS `'.$prefix.'user-sessions`; CREATE TABLE IF NOT EXISTS `'.$prefix.'user-sessions` (`id` int(11) NOT NULL AUTO_INCREMENT, `session_key` varchar(32) NOT NULL, `ip_address` varchar(15) NOT NULL, `expires` datetime NOT NULL, `last_seen` datetime NOT NULL, `browser` text NOT NULL, `user_id` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;')) return false;

    return true;
}


/**
 * Adds the show_page_information column to the settings table
 * 
 * @param object $Theamus
 * @return boolean
 */
function update_150($Theamus) {
    function update_150($Theamus) {
    $no = array();
    $columns = $Theamus->DB->fetch_rows(
        $Theamus->DB->custom_query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE `TABLE_SCHEMA`=DATABASE()"));
    
    foreach ($columns as $column) {
        if ($column['TABLE_NAME'] == $Theamus->DB->system_table("settings") && $column['COLUMN_NAME'] == "show_page_information") $no[] = "show_page_information";
        if ($column['TABLE_NAME'] == $Theamus->DB->system_table("links") && $column['COLUMN_NAME'] == "title") $no[] = "title";
        if ($column['TABLE_NAME'] == $Theamus->DB->system_table("links") && $column['COLUMN_NAME'] == "target") $no[] = "target";
    }
    
    if (!in_array("show_page_information", $no)) {
        if (!$Theamus->DB->custom_query("ALTER TABLE `{$Theamus->DB->system_table("settings")}` ADD `show_page_information` VARCHAR(500) NOT NULL after `favicon_path`")) {
            $Theamus->Log->system($Theamus->DB->get_last_error());
            return false;
        }
    }
    
    if (!in_array("title", $no)) {
        if (!$Theamus->DB->custom_query("ALTER TABLE `{$Theamus->DB->system_table("links")}` ADD `title` VARCHAR(500) NOT NULL AFTER `path`")) {
            $Theamus->Log->system($Theamus->DB->get_last_error());
            return false;
        }
    }
    
    if (!in_array("target", $no)) {
        if (!$Theamus->DB->custom_query("ALTER TABLE `{$Theamus->DB->system_table("links")}` ADD `target` VARCHAR(50) NOT NULL AFTER `path`")) {
            $Theamus->Log->system($Theamus->DB->get_last_error());
            return false;
        }
    }
    
    return true;
}
}