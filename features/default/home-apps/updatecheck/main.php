<?php

$error = false; // Start out the day nicely.

// Get the update information from the server
$json = file_get_contents("http://theamus.com/releases/api/update-info/");
$info = json_decode($json, true);

// Check the results
if ($info === null) {
    echo 'There was an issue retrieving the data from the server';
} else {
    if (is_array($info['old_versions'])) {
        if (in_array($Theamus->settings['version'], $info['old_versions'])) {
            $Theamus->notify("success", "There's an update available! - <a href='#' id='updatecheck_update-link'>Update Now!</a>");
        ?>
            <div style='border: 1px solid #EEE; margin: 10px 0; padding: 5px 5px 10px; overflow-y: auto; max-height: 250px;'>
                <?php echo $Theamus->Parsedown->text($info['notes']); ?>
            </div>
        <?php
        } else {
            echo 'There are no updates available at this time for your system.';
        }
    } else {
        echo 'There was an issue gathering update data.';
    }
}

?>

<script>
    admin_window_run_on_load("updatecheck_main");
</script>