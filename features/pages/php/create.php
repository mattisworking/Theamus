<?php

$post = filter_input_array(INPUT_POST);

// Get the page title
if (isset($post['title'])) {
    $title = htmlspecialchars(urldecode($post['title']));
    $alias = "";
    if ($title != "" ) {
        // Define the title and alias
        $clean_alias = preg_replace("/[^a-zA-Z0-9 ]/", '', htmlspecialchars_decode($title));
        $alias = strtolower(str_replace(" ", "_", trim($clean_alias)));

        // Check the database for an existing page
        $query_find_page = $tData->select_from_table($tData->prefix."pages", array("id"), array(
            "operator"  => "",
            "conditions"=> array("alias" => $alias)
        ));

        if ($tData->count_rows($query_find_page) > 0) {
            $error[] = "A page with this title/alias already exists.  Please choose another.";
        }
    } else {
        $error[] = "Please fill out the 'Page Title' field.";
    }
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

// Get new link
if (isset($post['create_link'])) {
    if ($post['create_link'] == "true") {
        $query_create_link = $tData->insert_table_row($tData->prefix."links", array(
            "alias"     => $alias,
            "text"      => $title,
            "path"      => $alias,
            "weight"    => 0,
            "groups"    => $groups,
            "type"      => "page",
            "location"  => "main",
            "child_of"  => 0
        ));

        if ($query_create_link == false) {
            $error[] = "There was an error when creating the link for this page.  The page was not created.";
        }
    }
}

// Show errors
if (!empty($error)) {
    alert_notify("danger", $error[0]);
} else {
    $query_create_page = $tData->insert_table_row($tData->prefix."pages", array(
        "alias"     => $alias,
        "title"     => $title,
        "content"   => $content,
        "permanent" => 0,
        "groups"    => $groups,
        "theme"     => $theme,
        "navigation"=> $nav
    ));

    if ($query_create_page != false) {
        alert_notify("success", "This page has been created. - ".js_countdown());
        run_after_ajax("back_to_pagelist");
    } else {
        alert_notify('danger', "There was an issue creating this page.");
    }
}