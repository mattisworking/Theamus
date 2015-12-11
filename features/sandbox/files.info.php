<?php

// this feature is only allowed to be accessed if the site is in developer mode
if ($Theamus->settings['developer_mode'] == 0) {
    die($Theamus->Call->error_page());
}

$feature['theme'] = "empty";
$feature['css']['file'][] = SANDBOX_DEV_MODE ? "dev/sandbox.css" : "sandbox.min.css";
$feature['js']['file'][] = SANDBOX_DEV_MODE ? "dev/sandbox.js" : "sandbox.min.js";

$Theamus->Call->load_class("sandbox.class.php", "Sandbox");

// File specification
switch ($Theamus->Call->get_called_file()) {
    case "index.php":
        $feature['title']  = "Theamus API Sandbox";
        break;
}