<?php

// Define default js and css file as empty array
$feature['js']['file'] 	= array();
$feature['css']['file']	= array();

// Files to deny basic users
$admin_files = array(
    "create.php",
    "remove.php",
    "save.php",
    "edit.php",
    "index.php",
    "groups-list.php",
    "remove-group.php"
    );
// Deny bad users
$tUser->deny_non_admins($file, $admin_files);

$feature['class']['file'] = 'groups.class.php';
$feature['class']['init'] = 'Groups';

define('FILE', $file);

// File specification
switch ($file) {
	case "index.php":
		$feature['js']['file'][] = "groups-index.js";
        $feature['css']['file'][] = "main.css";
		break;

	case "edit.php":
        $tUser->check_permissions("edit_groups");
		$feature['js']['file'][] = "groups-form.js";
		break;

    case "save.php":
        $tUser->check_permissions("edit_groups");
        break;

	case "create.php":
        $tUser->check_permissions("create_groups");
		$feature['js']['file'][] = "groups-form.js";
		break;

    case "remove.php":
        $tUser->check_permissions("remove_groups");
        break;

	default:
		break;
}

?>