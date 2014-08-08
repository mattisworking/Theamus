<?php

// Load the pages class
$feature['class']['file'] = 'pages.class.php';
$feature['class']['init'] = 'Pages';

define('FILE', $file); // Define the current file

// Load the file related information
switch ($file) {
    case 'index.php':
    case 'search.php':
    case 'create.php':
    case 'edit.php':
    case 'remove.php':
    case 'pages-list.php':
        // Deny anyone who isn't an administrator
        if (!$Theamus->User->is_admin()) !$ajax ? $Theamus->back_up() : die();

        $feature['css']['file'][] = PAGES_DEV_MODE ? 'dev/pages.admin.css' : 'pages.admin.min.css';
        $feature['js']['file'][] = PAGES_DEV_MODE ? 'dev/pages.admin.js' : 'pages.admin.min.js';
        break;

    case 'show-page.php':
        $feature['js']['script'][] = 'document.addEventListener("DOMContentLoaded", function() { prettyPrint(); });';
        break;
}