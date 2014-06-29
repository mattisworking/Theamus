<?php

$error = array(); // Empty error array
$get = filter_input_array(INPUT_GET); // Define and clean the GET information
$query_data = array("table" => $tData->prefix."media");

// Image ID
$id = 0; // Default for the table check later
if (isset($get['id'])) {
    if ($get['id'] != "" && is_numeric($get['id'])) {
        $id = $get['id'];
    } else {
        $error[] = "Invalid ID type.";
    }
} else {
    $error[] = "Unknown ID.";
}

// Check for database existance
$query_media = $tData->select_from_table($query_data['table'], array("path"), array(
    "operator"  => "",
    "conditions"=> array("id" => $id)
));
if ($query_media == false) {
    $error[] = "Error querying the database for existance.";
}
if ($tData->count_rows($query_media) == 0) {
    $error[] = "Cannot find this media item in the database.";
}

// Show errors
if (!empty($error)) {
    notify("admin", "failure", $error[0]);
} else {
    // Get the image information from the database
    $media = $tData->fetch_rows($query_media);

    // Remove the image, if it exists
    $path = ROOT."/media/images/".$media['path'];
    if (file_exists($path)) {
        unlink($path);
    }

    if ($tData->use_pdo == true) {
        $query_media->closeCursor();
    }

    $query = $tData->delete_table_row($query_data['table'], array(
        "operator"  => "",
        "conditions"=> array("id" => $id)
    ));

    // Check query
    if ($query != false) {
        notify("admin", "success", "Media removed successfully.");
    } else {
        notify("admin", "failure", "There was an error removing the media from the database.");
    }
}