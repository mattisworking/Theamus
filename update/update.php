<?php

/**
 * Function that will run updates to make Theamus the latest version
 *
 * @param array $system_info
 * @return boolean
 */
function update() {
    // Define the update 'functions' to run
    $updates = array('02', '11', '12', '130');

    // Run updates
    foreach ($updates as $update) {
        $update_function = 'update_'.$update;
        if (!$update_function()) return false;
    }

    update_version('1.3.0'); // Update the version

    update_cleanup(); // Cleanup!

    return true;
}
