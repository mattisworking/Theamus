<?php

// Define default js and css file as empty array
$feature['js']['file'] 	= array();
$feature['css']['file']	= array();

// Files to deny basic users
$admin_files = array(
    // Script files
    "upload.php",
    "remove.php",
    "save.php",

    // View files
    "features-list.php",
    "edit.php",
    "install.php",
    "index.php",
    "remove-feature.php"
    );
// Deny bad users
$tUser->deny_non_admins($file, $admin_files);

$feature['class']['file'] = 'features.class.php';
$feature['class']['init'] = 'Features';

define('FILE', $file);

// File specification
switch ($file) {
	case "index.php":
        $feature['js']['file'][] = "features-index.js";
        $feature['css']['file'][] = "main.css";
		break;

	case "install.php":
        $feature['js']['file'][] = "install.js";
        $feature['css']['file'][] = "main.css";
        $tUser->check_permissions("install_features");
		break;

    case 'edit.php' :
        $feature['js']['file'][] = "features-form.js";
        $tUser->check_permissions("edit_features");
        break;

    case "remove-feature.php":
        $tUser->check_permissions("remove_features");
        break;

    // Scripts
    case "save.php":
        if ($ajax != "script" || !$tUser->has_permission("edit_features")) die("Error.");

        $feature['class']['file'] = "features.class.php";
        $feature['class']['init'] = "Features";
        break;

    case "remove.php":
        if ($ajax != "script" || !$tUser->has_permission("remove_features")) die("Error.");

        $feature['class']['file'] = "features.class.php";
        $feature['class']['init'] = "Features";
        break;

    case "install/prelim.php":
        if ($ajax != "script" || !$tUser->has_permission("install_features")) die("Error.");

        $feature['class']['file'] = "features.class.php";
        $feature['class']['init'] = "Features";

        break;

    case "install/install.php":
        if ($ajax != "script" || !$tUser->has_permission("install_features")) die("Error.");

        $feature['class']['file'] = "features.class.php";
        $feature['class']['init'] = "Features";

        break;
}