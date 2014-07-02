<?php

// Define a default for the javascript and css files
$feature['js']['file']	= array();
$feature['css']['file']	= array();

$feature['class']['file'] = 'media.class.php';
$feature['class']['init'] = 'Media';

define('FILE', $file);

// Files to deny basic users
$admin_files = array(
    // Script files
    'upload.php',
    'remove-media.php',

    // View files
    'index.php',
    'media-list.php',
    'add-media.php'
    );
// Deny bad users
$tUser->deny_non_admins($file, $admin_files);

switch ($file) {
    case 'index.php':
        $feature['js']['file'][]  = 'media.js';
        $feature['css']['file'][] = 'media.css';
        break;

    case 'add-media.php':
        $feature['js']['file'][] = 'media.js';
        $feature['js']['file'][] = 'dnd.js';
        $feature['css']['file'][] = 'media.css';
        break;

    case 'upload.php':
        $tUser->check_permissions('add_media');
        break;

    case 'remove-media.php':
        $tUser->check_permissions('remove_media');
        break;

    default:
        break;
}