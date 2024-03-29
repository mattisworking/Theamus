<?php

// Administrators only can come to this feature
if ($ajax == false) $Theamus->back_up();
if (!$Theamus->User->is_admin()) die("You don't have permission to this feature.");

// Initialize the navigation class
$feature['class']['file'] = 'navigation.class.php';
$feature['class']['init'] = 'Navigation';

define('FILE', "navigation/{$Theamus->Call->get_called_file()}"); // Define the file as a global variable

$feature['js']['file'][] = NAV_DEV_MODE ? 'dev/navigation.admin.js' : 'navigation.admin.min.js';
$feature['css']['file'][] = NAV_DEV_MODE ? 'dev/navigation.admin.css' : 'navigation.admin.min.css';

// Define specific file information
switch ($Theamus->Call->get_called_file()) {
    case 'create.php':
        // Throw an exception for the people who want to create links but can't
        if (!$Theamus->User->has_permission('create_links')) throw new Exception('You do not have permission to Create Theamus Links');
        break;

    case 'edit.php':
        // Throw an exception for the people who want to create links but can't
        if (!$Theamus->User->has_permission('edit_links')) throw new Exception('You do not have permission to Edit Theamus Links');
        break;

    case 'remove.php':
        // Throw an exception for the people who want to create links but can't
        if (!$Theamus->User->has_permission('remove_links')) throw new Exception('You do not have permission to Remove Theamus Links');
        break;
}