<?php

// Look for the tData class in order to run these functions
if (class_exists('tData')) {
    /**
     * Function that will run updates to make Theamus the latest standard (1.3.1)
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

        update_version('1.4.2'); // Update the version

        update_cleanup(); // Cleanup!

        return true;
    }
} else {
    /**
     * Function that will run updates to make Theamus the latest standard (1.3.1)
     *
     * @param array $system_info
     * @return boolean
     */
    function update($Theamus, $update_information) {
        // Define the update 'functions' to run
        $updates = array();

        // Run updates
        foreach ($updates as $update) {
            $update_function = 'update_'.$update;
            if (!$update_function()) return false;
        }

        update_version($update_information, $Theamus); // Update the version

        update_cleanup($Theamus); // Cleanup!

        return true;
    }
}