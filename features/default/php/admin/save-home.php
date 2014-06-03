<?php

$query_data = array("table_name" => "dflt_home-apps", "data" => array(), "clause" => array());

// Get current user's info
$user = $tUser->user;

// Only do if a user is logged in
if ($user != false) {
    // Get apps
    if (isset($_GET['apps'])) {
        $get_apps = $_GET['apps'];
        if ($get_apps != "") {
            $get_apps = explode(",", $get_apps);
            foreach ($get_apps as $app) {
                $app = explode("=", $app);
                $apps[$app[0]] = $app[1];
            }
        } else {
            $error[] = "There are no apps to save.";
        }
    } else {
        $error[] = "There is an issue with the submitted apps; there are none.";
    }

    // Show errors
    if (!empty($error)) {
        alert_notify("danger", $error[0]);
    } else {
        // Save the apps
        foreach ($apps as $key => $val) {
            $query_data['data'][] = array("active" => $val);
            $query_data['clause'][] = array (
                "operator"  => "",
                "conditions"=> array("path" => $key)
            );
        }

        // Notify the user and get out
        if ($tData->update_table_row($query_data['table_name'], $query_data['data'], $query_data['clause'])) {
            alert_notify("success", "Your apps have been saved.");
        } else {
            alert_notify("danger", "There was an error saving the apps.");
        }
    }
}