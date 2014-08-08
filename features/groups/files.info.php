<?php

// Administrators only can come to this feature
if (!$Theamus->User->is_admin()) throw new Exception('Only administrators can access the Theamus Groups feature.');

// Initialize the groups class
$feature['class']['file'] = 'groups.class.php';
$feature['class']['init'] = 'Groups';

define('FILE', $file); // Define the file as a global variable

$feature['js']['file'][] = GROUPS_DEV_MODE ? 'dev/groups.admin.js' : 'groups.admin.min.js';
$feature['css']['file'][] = GROUPS_DEV_MODE ? 'dev/groups.admin.css' : 'groups.admin.min.css';

// Load the file related information
switch ($file) {
    case "edit.php":
        // Throw an exception for the people who want to edit groups but can't
        if (!$Theamus->User->has_permission('edit_groups')) throw new Exception('You do not have permission to Edit Theamus Groups');
        break;

    case "create.php":
        // Throw an exception for the people who want to create new groups but can't
        if (!$Theamus->User->has_permission('create_groups')) throw new Exception('You do not have permission to Create New Theamus Groups');
        break;

    case "remove.php":
        // Throw an exception for the people who want to delete groups but can't
        if (!$Theamus->User->has_permission('remove_groups')) throw new Exception('You do not have permission to Delete Theamus Groups');
        break;
}
