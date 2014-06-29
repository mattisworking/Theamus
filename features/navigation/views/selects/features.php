<?php

// Define the GET request information
$get = filter_input_array(INPUT_GET);

$get_feature = "";
if (isset($get['feature']) && $get['feature'] != "") {
    $get_feature = $get['feature'];
}

// Query the database for all available features
$query = $tData->select_from_table($tData->prefix."features", array("alias", "name"), array(), "ORDER BY `name` ASC");

// Check for a valid query
if ($query != false && $tData->count_rows($query) > 0) {
    $results = $tData->fetch_rows($query);
    $features = isset($results[0]) ? $results : array($results);

    // Loop through all of the features echoing them as options
    foreach ($features as $feature) {
        $selected = $get_feature == $feature['alias'] ? "selected" : "";
        echo "<option $selected value='".$feature['alias']."'>".$feature['name']."</option>";
    }
} else {
    // Throw out an error in the form of an option
    echo "<option>Error loading features</option>";
}