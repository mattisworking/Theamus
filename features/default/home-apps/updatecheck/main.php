<?php

$error = false; // Start out the day nicely.

// Define and check the system settings
$settings_query = $tData->query("SELECT * FROM `".$tDataClass->prefix."_settings`");
if (!$settings_query) {
    $error = "There was an error gathering information about the host system.";
} else {
    $settings = $settings_query->fetch_assoc();
}

// Make the AJAX call to the update server
//$server = "http://theamus.com/releases/update/";
$server = "http://localhost/theamus/release-manager/update-info/";
$info = $tDataClass->api(array(
    "type"  => "get",
    "url"   => $server,
    "method"=> array("Releases", "get_update_info"),
    "key"   => "dQPlembXjBfGvmCqH0Cot9uMeKAbRkTdr6ysWK1V50U="
));

// Check the results
if ($info['error']['status'] == 1) {
    $error = "There was an issue retrieving the data from the server";
} else {
    $update_data = $info['response']['data'];
}

if ($error != false) {
    echo $error;
} else {
    if (in_array($settings['version'], $update_data['old_versions'])) {
        notify("admin", "success", "There's an update available for your system!".
            " - <a href='#' onclick=\"return admin_go('settings', 'settings/settings/');\">Update Now!</a>");
    ?>
        <div style="border: 1px solid #EEE; margin: 10px 0; padding: 5px 5px 10px;
             overflow-y: auto; max-height: 150px;">
            <?=$update_data['notes']?>
        </div>
    <?php
    } else {
        echo 'There are no updates available at this time for your system.';
    }
}