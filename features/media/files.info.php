<?php

// Deny anyone who isn't an administrator
if (!$Theamus->User->is_admin()) !$ajax ? $Theamus->back_up() : die();

// Load the pages class
$feature['class']['file'] = 'media.class.php';
$feature['class']['init'] = 'Media';

define('FILE', $file); // Define the current file

// Add the JS and CSS
$feature['css']['file'][] = MEDIA_DEV_MODE ? 'dev/media.admin.css' : 'media.admin.min.css';
$feature['js']['file'][] = MEDIA_DEV_MODE ? 'dev/media.admin.js' : 'media.admin.min.js';

switch ($file) {
    case 'index.php':
        break;

    case 'add-media.php':
        $feature['js']['file'][] = MEDIA_DEV_MODE ? 'dev/dnd.js' : 'dnd.min.js';
        break;

    case 'upload.php':
        if (!$Theamus->User->has_permission('add_media')) throw new Exception('You do not have permission to Add Theamus Media.');
        break;

    case 'remove-media.php':
        if (!$Theamus->User->has_permission('remove_media')) throw new Exception('You do not have permission to Delete Theamus Media.');
        break;
}