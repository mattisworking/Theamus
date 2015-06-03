<?php

$feature['css']['file'][] = INSTALL_DEV_MODE ? 'dev/install.css' : 'install.min.css';

// Define the feature view files
$view_files = array(
    'index.php',
    'dependencies.php',
    'setup.php');

// Don't show errors for the API files so we get an actual error in the returned data
if ($ajax != false) ini_set('display_errors', 0);

// Die on anyone trying to make a request to a file that isn't defined here
if (!in_array($Theamus->Call->get_called_file(), $view_files)) {
    $ajax == false ? $Theamus->back_up() : die();
}

switch ($Theamus->Call->get_called_file()) {
    case 'index.php':
        $feature['title']   = 'Welcome';
        $feature['header']  = '';

        $feature['theme']   = 'homepage';
        break;

    case 'dependencies.php':
        $feature['title'] = 'Theamus Dependencies';
        $feature['header'] = 'Dependencies Check';
        break;

    case 'setup.php':
        $feature['title'] = 'Install Setup';
        $feature['header'] = 'Install Setup';

        $feature['js']['file'][] = INSTALL_DEV_MODE ? 'dev/install.js' : 'install.min.js';
        break;
}