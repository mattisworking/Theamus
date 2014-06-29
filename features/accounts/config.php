<?php

// Define custom feature folders
$feature['js']['folder']        = 'js';
$feature['css']['folder']       = 'css';
$feature['scripts']['folder']   = 'php';
$feature['class']['folder']     = 'php';

// Custom function file to load beforehand
//$feature['functions']['file'] = 'php/functions.php';
$feature['api']['class_file'] = array('php/accounts.class.php');

// Define feature information
$feature['alias']       = 'accounts';
$feature['name']        = 'Accounts';
$feature['db_prefix']   = '';
$feature['groups']      = array('everyone');
$feature['version']     = '1.2';
$feature['notes']       = array(
    'Rewrote the entire feature from the ground up to work with the latest Theamus standards',
    'Updated the feature styling to be more comfortable for users and to fit the new admin panel'
);

// Define the author information
$feature['author']['name']      = 'Eyrah Temet';
$feature['author']['alias']     = 'Eyraahh';
$feature['author']['email']     = 'eyrah.temet@theamus.com';
$feature['author']['company']   = 'Theamus';

// Define configuration scripts
/*
$feature['install']['script']   = 'config/install.php';
$feature['update']['script']    = 'config/update.php';
$feature['remove']['script']    = 'config/remove.php';
 */
