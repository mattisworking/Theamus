<?php

/**
 * Function that will run updates to make Theamus the latest version
 *
 * @param array $system_info
 * @return boolean
 */
function update($system_info) {
    // Run updates
    update_02();
    update_11();
    update_12();
    update_version("1.2");
    update_cleanup();

    return true;
}