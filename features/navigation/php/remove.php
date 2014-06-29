<?php

$post = filter_input_array(INPUT_POST); // Clean the incoming information
$query_data = array("table" => $tData->prefix."links");
$error = array(); // Error checking
$id = ""; // Default ID for the check query later

// Link ID
if (isset($post['link_id']) && $post['link_id'] != "") {
    $id = $post['link_id'];
} else {
    $error[] = "Unknown link ID.";
}

// Query the database for the link
$query_find_link = $tData->select_from_table($query_data['table'], array("id"), array(
    "operator"  => "",
    "conditions"=> array("id" => $id)
));
if ($query_find_link != false && $tData->count_rows($query_find_link) == 0) {
    $error[] = "There was an issue finding the link in question.";
}

// Show errors
if (!empty($error)) {
    notify("admin", "failure", $error[0]);
} else {
    $query = $tData->delete_table_row($query_data['table'], array(
        "operator"  => "",
        "conditions"=> array("id" => $id)
    ));


    // Check the query and respond accordingly
    if ($query != false) {
        notify("admin", "success", "This link has been removed successfully.");
    } else {
        notify("admin", "failure", "There was an error deleting this link.");
    }
}
