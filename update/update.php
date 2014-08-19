<?php

/**
 * Function that will run updates to make Theamus the latest version
 *
 * @param array $system_info
 * @return boolean
 */
function update($Theamus, $update_info) {
    // Run updates
    update_02($Theamus);
    update_11($Theamus);
    update_12($Theamus);
    update_version($Theamus, $update_info);
    update_cleanup($Theamus);

    return true;
}