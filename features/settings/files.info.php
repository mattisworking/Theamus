<?php

// Don't allow anyone that isn't an administrator
if (!$tUser->is_admin() && $ajax == false) {
    back_up();
} elseif (!$tUser->is_admin() && $ajax != false && $ajax != "api") {
    die("Invalid permission");
} elseif ($ajax != "api") {
    $feature['class']['file'] = "settings.class.php";
    $feature['class']['init'] = "Settings";
}

// Allowed API 'files' to be called
$allowed_api = array("update-check.php", "auto-update.php");
if (!in_array($file, $allowed_api) && $ajax == "api") {
    $this->api_fail = "You do not have access to this via API.";
}

// Define file specifics
switch ($file) {
    case "index.php" :
        $feature['js']['file'][] = "site.js";
        $feature['js']['file'][] = 'customization.js';
        break;

    case "settings.php" :
        $feature['css']['file'][] = "settings.css";

        $feature['js']['file'][] = "site.js";
        $feature['js']['file'][] = 'settings.js';
        break;

    case "update-manually.php":
        $feature['js']['file'][] = "settings.js";
        break;

    case "about-theamus.php":
        if ($ajax != "include" || $location != "admin") {
            go_back();
        }

        $feature['css']['file'][] = "about.css";
        break;

    default :
        break;
}