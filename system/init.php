<?php

/**
 * Defines database variable constants to lower the database calls made
 */
function initiate_db_variables() {
    $tData = new tData();
    $tData->connect(true);
    define('DB_PREFIX', $tData->get_system_prefix().'_');
    
    $settings = $tData->select_from_table(DB_PREFIX.'settings');
    if ($settings) {
        define('TM_SETTINGS', serialize($tData->fetch_rows($settings)));
    }
}


/**
 * Initiates the page.
 * What you see on the web is a direct result of this function
 */
function initiate() {
    $params = isset($_GET['params']) ? $_GET['params'] : "";
    
    $tCall = new tCall($params);
    $tCall->handle_call();
}

initiate_db_variables();
initiate();