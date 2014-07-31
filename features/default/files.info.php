<?php

$feature['js']['file'] = array();
$feature['css']['file'] = array();

$HomePage = new HomePage($Theamus);
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