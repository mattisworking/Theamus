<?php

$error = false; // Start out the day nicely.

// Make the AJAX call to the update server
$server = 'http://theamus.com/release-manager/update-info/';
$info = $Theamus->API->api(array(
    'type'  => 'get',
    'url'   => $server,
    'method'=> array('Releases', 'get_update_info'),
    'key'   => 'dQPlembXjBfGvmCqH0Cot9uMeKAbRkTdr6ysWK1V50U='
));

// Check the results
if ($info['error']['status'] == 1) {
    $error = 'There was an issue retrieving the data from the server';
} else {
    $update_data = $info['response']['data'];
}

if ($error != false) {
    echo $error;
} else {
    if (is_array($update_data['old_versions'])) {
        if (in_array($Theamus->settings['version'], $update_data['old_versions'])) {
            notify('admin', 'success', 'There\'s an update available for your system!'.
                ' - <a href="#" onclick="return admin_go(\'settings\', \'settings/settings/\');\">Update Now!</a>');
        ?>
            <div style='border: 1px solid #EEE; margin: 10px 0; padding: 5px 5px 10px; overflow-y: auto; max-height: 150px;'>
                <?=$update_data['notes']?>
            </div>
        <?php
        } else {
            echo 'There are no updates available at this time for your system.';
        }
    } else {
        echo 'There was an issue gathering update data.';
    }
}