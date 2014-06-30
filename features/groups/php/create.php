<?php

$error = array(); // Define an empty error array

$post = filter_input_array(INPUT_POST); // Define a filtered post array
$get = filter_input_array(INPUT_GET);   // Define a filtered get array

$query_data = array("table" => $tData->prefix."groups", "data" => array());

// Get group name
if ($post['name'] != "") {
    $name = urldecode($post['name']);
    if (!preg_match("/[^a-zA-Z '_-]/", $name)) { // Check for illegal characters
        // Clean and define the alias and name
        $alias = str_replace(" ", "_", strtolower($name));

        // Query the database for a group with this name
        $query_group = $tData->select_from_table($query_data['table'], array("alias"), array(
            "operator"  => "",
            "conditions"=> array("alias" => $alias)));

        if ($query_group != false) {
            if ($tData->count_rows($query_group) > 0) {
                $error[] = "A group with that name already exists";
            }
        } else {
            $error[] = "There was an error querying the database to check the name.";
        }
    } else {
        $error[] = "The group's name can only contain alphabet letters and spaces.";
    }
} else {
    $error[] = "Please fill out the 'Group Name' field.";
}

// Get permissions
$permissions = "";
if ($post['permissions'] != "" || $post['name'] == "Everyone") {
    $permissions = urldecode($post['permissions']);
}

if (!empty($error)) { // Show errors
	alert_notify('danger', $error[0]);
} else {
    // Define the creation query data
    $query_data['data'] = array(
        "alias"         => $alias,
        "name"          => $name,
        "permissions"   => $permissions,
        "home_override" => "false"
    );

	// Query to create
    $query = $tData->insert_table_row($query_data['table'], $query_data['data']);

	// Notify user and get out
    if ($query != false) {
        alert_notify('success', "This group has been created.<br/>".js_countdown());
        run_after_ajax("back_to_grouplist");
    } else {
        alert_notify('danger', "There was an error querying the database to create.");
    }
}