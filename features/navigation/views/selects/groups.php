<?php

$get = filter_input_array(INPUT_GET);

// Groups
if (isset($get['groups']) && $get['groups'] != "") {
    $groups = explode(",", urldecode($get['groups']));
} else {
    $groups = array();
}

// Query the database for groups
$query = $tData->select_from_table($tData->prefix."groups", array("alias", "name"));
$results = $tData->fetch_rows($query);
$all_groups = isset($results[0]) ? $results : array($results);

// Loop through all groups, showing as options
foreach ($all_groups as $group) {
    if (empty($groups)) {
        $selected = $group['alias'] == "everyone" ? "selected" : "";
    } else {
        $selected = in_array($group['alias'], $groups) ? "selected" : "";
    }
    echo "<option ".$selected." value='".$group['alias']."'>"
            .$group['name']."</option>";
}