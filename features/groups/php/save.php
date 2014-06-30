<?php

$error = array(); // Define an empty error array

$post = filter_input_array(INPUT_POST); // Define a filtered post array
$get = filter_input_array(INPUT_GET);   // Define a filtered get array

$query_data = array("table" => $tData->prefix."groups", "data" => array(), "clause" => array());

// Get group ID
if ($post['group_id'] != "") {
    $id = $post['group_id'];
} else {
    $error[] = "There was an error finding the group's ID.";
}

// Get group name
if ($post['name'] != "") {
    $name = urldecode($post['name']);
    if (!preg_match("/[^a-zA-Z '_-]/", $name)) { // Check for illegal characters
        // Clean and define the alias and name
        $alias = str_replace(" ", "_", strtolower($name));
    } else {
        $error[] = "There are invalid characters in the group name.";
    }
} else {
    $error[] = "Please fill out the 'Group Name' field.";
}

// Get permissions
$permissions = "";
if ($post['permissions'] != "" || $post['name'] == "Everyone") {
    $permissions = urldecode($post['permissions']);
}

// Get home page
if ($get['home'] != "") {
    $home = str_replace("{p}", ".", str_replace("{d}", "-", str_replace("{fs}", "/", urldecode($get['home']))));
} else {
    $error[] = "There's something wrong with the home page selection.";
}

// Show errors
if (!empty($error)) {
    alert_notify('danger', $error[0]);

// Update group
} else {
    $query_data['data'] = array(
        "alias" => $alias,
        "name"  => $name,
        "permissions"   => $permissions,
        "home_override" => $home
    );
    $query_data['clause'] = array(
        "operator"  => "",
        "conditions"=> array(
            "id" => $id
        )
    );

    $query = $tData->update_table_row($query_data['table'], $query_data['data'], $query_data['clause']);


    if ($query != false) {
        alert_notify('success', "This information has been saved.");
    } else {
        alert_notify('danger', "There was an error querying the database.");
    }
}