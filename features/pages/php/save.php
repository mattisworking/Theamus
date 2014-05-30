<?php

$post = filter_input_array(INPUT_POST);
$query_data = array(
    "table" => $tData->prefix."_pages",
    "data"  => array(),
    "clause"=> array()
);

// Get the page ID
if (isset($post['page_id'])) {
    $id = $post['page_id'];
    if ($id != "") {

        // Get the page information
        $query_page = $tData->select_from_table($query_data['table'], array("alias"), array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        if ($query_page != false) {
            $page = $tData->fetch_rows($query_page);
        } else {
            $error[] = "There was an error finding the page information in the database.";
        }
    } else {
        $error[] = "Invalid ID value.";
    }
}

// Get the page title
if (isset($post['title'])) {
    $title = htmlspecialchars(urldecode($post['title']));
    $alias = "";
    if ($title != "" ) {
        // Define the title and alias
        $clean_alias = preg_replace("/[^a-zA-Z0-9 ]/", '', htmlspecialchars_decode($title));
        $alias = strtolower(str_replace(" ", "_", trim($clean_alias)));

        // Check the database for an existing page
        if ($alias != $page['alias']) {
            $query_alias = $tData->select_from_table($query_data['table'], array("id"), array(
                "operator"  => "",
                "conditions"=> array("alias" => $alias)
            ));
            if ($tData->count_rows($query_alias) > 0) {
                $error[] = "A page with this title/alias already exists.  Please choose another.";
            }
        }
    } else {
        $error[] = "Please fill out the 'Page Title' field.";
    }
}

// Find the a page link with the old alias in the links database
$link_query = $tData->select_from_table($tData->prefix."_links", array("id"), array("operator" => "", "conditions" => array("[%]path" => $page['alias']."%")));
if ($link_query == false) {
    echo notify("admin", "failure", "There was an querying the link database.");
    die();
}


// Get the page content
if (isset($post['content'])) {
    $content = urldecode($post['content']);
    if ($content == "") {
        $error[] = "Please give this page some content.";
    }
}

// Get groups
if (isset($post['groups'])) {
    $groups = urldecode($post['groups']);
    if ($groups == "") {
        $groups = "everyone";
    }
}

// Get theme
if (isset($post['layout'])) {
    $theme = urldecode($post['layout']);
}

// Get navigation
if (isset($post['navigation'])) {
    $nav = urldecode($post['navigation']);
}

// Show errors
if (!empty($error)) {
    notify("admin", "failure", $error[0]);
} else {
    $query_data['data'] = array(
        "alias"     => $alias,
        "title"     => $title,
        "content"   => $content,
        "groups"    => $groups,
        "theme"     => $theme,
        "navigation"=> $nav
    );
    $query_data['clause'] = array(
        "operator"  => "",
        "conditions"=> array("id" => $id)
    );

    $query = $tData->update_table_row($query_data['table'], $query_data['data'], $query_data['clause']);

    if ($query != false) {
        if ($tData->count_rows($link_query) > 0) {
            $link = $tData->fetch_rows($link_query);
            $tData->show_query_errors = true;
            $update_link = $tData->update_table_row($tData->prefix."_links", array("path" => $alias), array(
                "operator"      => "",
                "conditions"    => array("id" => $link['id'])
            ));

            if ($update_link == false) {
                notify("admin", "failure", "The links related to this page could not be updated.");
            } else {
                notify("admin", "success", "Links related to this page have been updated.");
            }
        }

        notify("admin", "success", "The changes to this page have been saved.");
    } else {
        notify("admin", "failure", "There was an issue saving this information.");
    }
}