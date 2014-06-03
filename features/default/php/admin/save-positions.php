<?php

function get_item_sql($column, $items) {
    $query_data = array();

    // Loop through all of the items
    foreach ($items as $item) {
        // Split the item to get the path/position
        $info = explode("=", $item);

        // Define the query information
        $query_data[] = array(
            "set"       => array("position" => $info[1], "column" => $column),
            "clause"    => array("operator" => "", "conditions" =>  array("path" => $info[0]))
        );
    }

    return $query_data;
}

function get_sql($column) {
    // Clean the GET
    $get = filter_input_array(INPUT_GET);

    // Make sure this variable is available to us
    if (!isset($get["column".$column])) {
        die(notify("admin", "failure", "There was an issue finding the column."));
    }

    // Don't do anything if there's nothing to do
    if ($get["column".$column] == "") {
        return false;

    // Define the column items and return the sql
    } else {
        $items = explode(",", $get["column".$column]);
        return get_item_sql($column, $items);
    }
}

$user = $tUser->user;

if ($user != false) {
    $query_data = array_merge(get_sql("1"), get_sql("2"));

    $query_data['data'] = $query_data['clause'] = array();
    foreach ($query_data as $qd) {
        if (isset($qd['set']) && isset($qd['clause'])) {
            $query_data['data'][]     = $qd['set'];
            $query_data['clause'][]   = $qd['clause'];
        }
    }

    if ($tData->update_table_row("dflt_home-apps", $query_data['data'], $query_data['clause'])) {
        alert_notify('success', "Your home page has been saved.");
    } else {
        alert_notify('danger', "There was an error saving the home page.");
    }
}