<?php

// Define default files
$feature['js']['file']	= array();
$feature['css']['file']	= array();

// Files to deny basic users
$admin_files = array(
    // Script files
    "create.php",
    "remove.php",
    "save.php",

    // View files
    "edit.php",
    "form.php",
    "index.php",
    "navigation-list.php",
    "remove-link.php"
    );
// Deny bad users
$tUser->deny_non_admins($file, $admin_files);

$feature['class']['file'] = "navigation.class.php";
$feature['class']['init'] = "Navigation";

define('FILE', $file);

// Define specific file information
switch ($file) {
	case "index.php":
		$feature['js']['file'][] = "navigation-index.js";
        $feature['css']['file'][] = "main.css";
		break;

	case "create.php":
		$feature['js']['file'][] = "navigation-form.js";
		break;

	case "edit.php":
		$feature['js']['file'][] = "navigation-form.js";
		break;

	default:
		break;
}