<?php

$post = filter_input_array(INPUT_POST); // Clean request information
$error = array(); // Error checking array

// Theme ID
if ($post['theme_id'] != "") {
    $id = $post['theme_id'];
} else {
    $error[] = "Unknown theme ID.";
}

// Query the database for this theme
$query_find_theme = $tData->select_from_table($tData->prefix."themes", array("alias"), array(
    "operator"  => "",
    "conditions"=> array("id" => $id)
));

if ($query_find_theme == false || $tData->count_rows($query_find_theme) == 0) {
    $error[] = "Theme not found!";
}

// Show errors
if (!empty($error)) {
	alert_notify("danger", $error[0]);
} else {
	// Get the theme information
	$theme = $tData->fetch_rows($query_find_theme);

	// Define the theme folder
	$path = path(ROOT."/themes/".$theme['alias']);

    $this->tData->show_query_errors = true;
    $this->tData->use_pdo == false ? $this->tData->db->autocommit(false) : $this->tData->db->beginTransaction();
    $query_remove_theme = $tData->delete_table_row($tData->prefix."themes", array(
        "operator"  => "",
        "conditions"=> array("id" => $id)
    ));

    $query_remove_data = $tData->delete_table_row($tData->prefix."themes-data", array(
        "operator"  => "",
        "conditions"=> array("theme" => $theme['alias'])
    ));

    if ($query_remove_theme != false && $query_remove_data != false) {
        if ($tFiles->remove_folder($path)) {
            $this->tData->db->commit();
            alert_notify("success", "This theme has been removed.");
        } else {
            $this->tData->use_pdo == false ? $this->tData->db->rollback() : $this->tData->db->rollBack();
            alert_notify("danger", "There was an issue when removing this theme.");
        }
    } else {
        alert_notify("danger", "There was an issue when removing this theme from the database.");
    }
}