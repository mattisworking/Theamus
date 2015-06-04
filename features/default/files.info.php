<?php

// Define the homepage stuffs
$HomePage = new HomePage($Theamus);
if ($file == 'index.php') $i = $HomePage->redirect();

// Load the admin class if relevant
if ($Theamus->User->is_admin() && ($folders[0] == 'admin' || $file == 'admin-index.php')) {
    if ($ajax == false) $Theamus->back_up();
    define('FILE', "default/{$file}");

    $feature['class']['file'] = 'admin.class.php';
    $feature['class']['init'] = 'DefaultAdmin';
} elseif (!$Theamus->User->is_admin() && ($folders[0] == 'admin' || $file == 'admin-index.php')) {
    $ajax == false ? $Theamus->back_up() : die();
}

// Load the file related information
switch ($file) {
    case 'index.php':
        $feature['title']   = $i['title'];
        $feature['header']  = $i['title'];

        $feature['js']['file'][] = DFLT_DEV_MODE ? 'dev/init.js' : 'init.min.js';

        $feature['theme']   = $i['theme'];
        $feature['nav']     = $i['navigation'];
        break;

    case 'admin-index.php':
        $feature['css']['file'][]   = DFLT_DEV_MODE ? 'admin/admin-home.css' : 'admin/admin-home.min.css';
        $feature['js']['file'][]    = DFLT_DEV_MODE ? 'dev/admin-home.js' : 'admin-home.min.js';
        break;

    default: $feature['title'] = $feature['header'] = '';
}