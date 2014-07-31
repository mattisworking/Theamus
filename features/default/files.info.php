<?php

$feature['js']['file'] = array();
$feature['css']['file'] = array();

// Files to deny basic users
$admin_files = array(
    // Script files
    "admin/save-home.php",
    "admin/save-positions.php",
    "admin/update-apps.php",

    // View files
    "adminHome.php",
    "admin/apps.php",
    "admin/choice-window.php"
    );
// Deny bad users
$tUser->deny_non_admins($file, $admin_files);

$HomePage = new HomePage();
if ($file == "index.php") {
    $i = $HomePage->redirect();
}

switch ($file) {
    case "index.php":
        $feature['title'] = $i['title'];
        $feature['header'] = $i['title'];
        $feature['js']['file'][] = 'init.js';
        $feature['theme'] = $i['theme'];
        $feature['nav'] = $i['navigation'];
        break;

    case "adminHome.php":
        $feature['css']['file'][] = "admin/admin-home.css";
        $feature['js']['file'][] = "admin-home.js";
        break;

    default :
        $feature = true;
        break;
}