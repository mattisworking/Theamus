<?php

$error = array(); // Error checking array
$post  = filter_input_array(INPUT_POST); // Clean post input
$query_data = array(
    "table" => $tData->prefix."links",
    "data"  => array(),
    "clause"=> array()
);

$id = ""; // Default for check query later on

// Link ID
if ($post['link-id'] != "") {
    $id = $post['link-id'];

    // Get the link information
    $query_find_link = $tData->select_from_table($query_data['table'], array("alias"), array(
        "operator"  => "",
        "conditions"=> array("id" => $id)
    ));
    if ($query_find_link != false) {
        $link = $tData->fetch_rows($query_find_link);
    } else {
        $error[] = "There was an error finding the link information in the database.";
    }
} else {
    $error[] = "Unkown link ID.";
}

// Link text
if ($post['text'] != "") {
    $text = htmlspecialchars(urldecode($post['text']));

    // Check for the length of the link text
    if (strlen($text) < 2) {
        $error[] = "The link text must be at least 2 characters long.";
    } else {
        // Define the text and the alias
        $clean_alias = preg_replace("/[^a-zA-Z0-9 ]/", '', htmlspecialchars_decode($text));
        $alias = strtolower(str_replace(" ", "_", trim($clean_alias)));

        // Check the database for an existing link
        if ($alias != $link['alias']) {
            $query_check_link = $tData->select_from_table($query_data['table'], array("id"), array(
                "operator"  => "",
                "conditions"=> array("alias", $alias)
            ));
            if ($query_check_link != false) {
                if ($tData->count_rows($query_check_link) > 0) {
                    $error[] = "A link with this text/alias already exists.  Please choose another.";
                }
            }
        }
    }
} else {
    $error[] = "Please fill out the 'Link Text' field.";
}

// Link Path
if ($post['path-type'] != "") {
    // Define the path type
    $pt_exploded = explode("-", $post['path-type']);
    $type        = $pt_exploded[1];

    // Define the path based on the type selected
    switch ($type) {
        case "null":
            $path = $post['null'];
            break;
        case "url":
            $path = $post['url'];
            break;
        case "page":
            $path = $post['page'];
            break;
        case "feature":
            $path = $post['feature']."/".$post['file'];
            break;
        case "js":
            $path = $post['js'];
            break;
        default:
            $path = $post['url'];
    }

    // Check for a path
    if ($path != "") {
        // Clean the path variable
        if($type == "null") {
            $path = "javascript:void(0);";
        } else {
            $path = trim(urldecode($path), "/");
        }
        if ($type != "js" && $type != "null") {
            $path = $path."/";
        }
    } else {
        $error[] = "Please choose a path for this link to go to.";
    }
}

// Position
$position = urldecode($post['position']);

// Child of
$child_of = urldecode($post['child_of']);

// Link weight
$weight = $post['weight'];

// Link groups
if ($post['groups'] != "") {
    $groups = urldecode($post['groups']);
} else {
    $groups = "everyone";
}

// Show errors
if (!empty($error)) {
	alert_notify("danger", $error[0]);
} else {
    $query_data['data'] = array(
        "alias"     => $alias,
        "text"      => $text,
        "path"      => $path,
        "weight"    => $weight,
        "groups"    => $groups,
        "type"      => $type,
        "location"  => $position,
        "child_of"  => $child_of
    );
    $query_data['clause'] = array(
        "operator"  => "",
        "conditions"=> array("id" => $id)
    );

    $query = $tData->update_table_row($query_data['table'], $query_data['data'], $query_data['clause']);

    // Check for a good query and notify the user good/bad/otherwise
    if ($query != false) {
        alert_notify("success", "This information has been saved.");
    } else {
        alert_notify("danger", "There was an error saving this information.");
    }
}