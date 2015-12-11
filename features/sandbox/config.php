<?php

define("SANDBOX_DEV_MODE", TRUE);

$Theamus->Call->set_feature_config(array(
    // System Required
    "theamus_version" => "1.3.0",
    "permissions"     => array("database", "files"),

    // Feature Required
    "folder_name"     => "sandbox",
    "feature_name"    => "Sandbox",
    "feature_version" => "0.0.0",
    "custom_folders"  => array("php" => "php", "javascript" => "js", "css" => "css", "class" => "php"),

    // Feature Optional
    "load_files" => array("api" => array("php/sandbox.class.php")),
    "release_notes" => array(
        "1.0.0" => array("Initial release.")
    ),

    // Optional Author Information
    "author" => array(
        "name"    => array("matt"),
        "alias"   => array("helllomatt"),
        "email"   => array("me@helllomatt.com"),
        "company" => "Theamus"
    )
));