<?php

// Administrators only can come to this feature
if (!$Theamus->User->is_admin()) throw new Exception('Only administrators can access the Theamus Appearance feature.');

// Initialize the appearance class
$feature['class']['file'] = 'appearance.class.php';
$feature['class']['init'] = 'Appearance';

define('FILE', "appearance/{$Theamus->Call->get_called_file()}"); // Define the file as a global variable

$feature['js']['file'][] = APPEARANCE_DEV_MODE ? 'dev/appearance.admin.js' : 'appearance.admin.min.js';
$feature['css']['file'][] = APPEARANCE_DEV_MODE ? 'dev/appearance.admin.css' : 'appearance.admin.min.css';

// Customize files
switch ($Theamus->Call->get_called_file()) {
    case 'install.php':
        // Throw an exception for the people who want to install themes but can't
        if (!$Theamus->User->has_permission('install_themes')) throw new Exception('You do not have permission to Install Theamus Themes');
        break;

    case 'edit.php':
        // Throw an exception for the people who want to edit themes but can't
        if (!$Theamus->User->has_permission('edit_themes')) throw new Exception('You do not have permission to Edit Theamus Themes');
        break;

    case 'remove.php':
        // Throw an exception for the people who want to remove themes but can't
        if (!$Theamus->User->has_permission('remove_themes')) throw new Exception('You do not have permission to Remove Theamus Themes');
        break;
}