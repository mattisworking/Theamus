<?php

// Administrators only can come to this feature
if (!$Theamus->User->is_admin()) throw new Exception('Only administrators can access the Theamus Navigation feature.');

// Initialize the navigation class
$Theamus->Call->load_class("settings.class.php", "Settings");

define('FILE', "settings/{$file}"); // Define the file as a global variable

$feature['js']['file'][] = SETTINGS_DEV_MODE ? 'dev/settings.admin.js' : 'settings.admin.min.js';
$feature['css']['file'][] = SETTINGS_DEV_MODE ? 'dev/settings.admin.css' : 'settings.admin.min.css';

if ($folders[count($folders) - 1] == "doc") $Theamus->go_back();

// Define file specifics
switch ($file) {
    case "index.php":
        break;

    case "settings.php":
        break;

    case "update-manually.php":
        break;

    case "about-theamus.php":
        if ($ajax != "include" || $location != "admin") $Theamus->go_back();
        $feature['css']['file'][] = SETTINGS_DEV_MODE ? 'dev/about.css' : 'about.min.css';
        break;
        
    case "logs/view.php":
        $feature['js']['file'][] = SETTINGS_DEV_MODE ? "dev/logs.admin.js" : "logs.admin.min.js";
        break;
        
    case "logs/listing.php":
        $Theamus->Call->load_class("logs.class.php", "Logs");
        $feature['css']['file'][] = SETTINGS_DEV_MODE ? "dev/logs.admin.css" : "logs.admin.min.css";
        break;
}