<?php

// Administrators only can come to this feature
if (!$Theamus->User->is_admin()) throw new Exception('Only administrators can access the Theamus Features feature.');

// Initialize the features class
$feature['class']['file'] = 'features.class.php';
$feature['class']['init'] = 'Features';

define('FILE', $file); // Define the file as a global variable

$feature['css']['file'][]   = FEATURES_DEV_MODE ? 'dev/features.admin.css' : 'features.admin.min.css';
$feature['js']['file'][]    = FEATURES_DEV_MODE ? 'dev/features.admin.js' : 'features.admin.min.js';


// File specification
switch ($file) {
    case 'install.php':
        if (!$Theamus->User->has_permission('install_features')) throw new Exception('You do not have permission to Install Theamus Features');
        break;

    case 'edit.php':
        if (!$Theamus->User->has_permission('edit_features')) throw new Exception('You do not have permission to Edit Theamus Features');
        break;

    case 'remove.php':
        if (!$Theamus->User->has_permission('remove_features')) throw new Exception('You do not have permission to Remove Theamus Features');
        break;
}