<?php

define("<folder_dev_mode>", TRUE);

$Theamus->Call->set_feature_config(array(
    // System Required
    "theamus_version" => "1.3.0",
    "permissions"     => array("database", "files"),

    // Feature Required
    "folder_name"     => "<folder_name>",
    "feature_name"    => "<feature_name>",
    "feature_version" => "0.0.0",
    "custom_folders"  => array("php" => "php", "javascript" => "js", "css" => "css", "class" => "php"),

    // Feature Optional
    "load_files" => array(),
    "release_notes" => array(
        "0.0.0" => array("Currently in development.")
    ),

    // Optional Author Information
    "author" => array(
        "name"    => array("<user_name>"),
        "alias"   => array("<user_username>"),
        "email"   => array("<user_email>"),
        "company" => ""
    )
));