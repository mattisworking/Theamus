<?php

$feature['js']['file'] 	= array();
$feature['css']['file'] = array();

// Files to deny basic users
$admin_files = array(
    // Script files
    "create.php",
    "remove.php",
    "save.php",

    // View files
    "add.php",
    "edit.php",
    "pages-list.php",
    "index.php"
    );
// Deny bad users
$tUser->deny_non_admins($file, $admin_files);

$feature['class']['file'] = "pages.class.php";
$feature['class']['init'] = "Pages";

define('FILE', $file);

switch ($file) {
    case "index.php":
        $feature['js']['file'][] = "pages-index.js";
        $feature['css']['file'][] = "main.css";
        break;

    case "create.php":
        $feature['js']['file'][] = "pages-form.js";
        $feature['css']['file'][] = "main.css";
        break;

    case "edit.php":
        $feature['js']['file'][] = "pages-form.js";
        $feature['css']['file'][] = "main.css";
        break;

    case "show-page.php":
        $feature['js']['script'][] = "document.addEventListener('DOMContentLoaded', function() { prettyPrint(); });";
        break;
}