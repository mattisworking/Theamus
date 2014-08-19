<?php

define('APPEARANCE_DEV_MODE', FALSE);

$Theamus->Call->set_feature_config(array(
    // System Required
    'theamus_version' => '1.3.0',
    'permissions'     => array('database', 'files'),

    // Feature Required
    'folder_name'     => 'appearance',
    'feature_name'    => 'Theamus Appearance',
    'feature_version' => '1.3.0',
    'custom_folders'  => array(
        'php'        => 'php',
        'javascript' => 'js',
        'css'        => 'css',
        'class'      => 'php'
    ),

    // Feature Optional
    'load_files' => array(
        'api'       => array('php/appearance.class.php'),
    ),
    'release_notes' => array(
        '1.3.0' => array('Updated to the new way of doing things.'),
        '1.0' => array('Initial release.')
    ),

    // Optional Author Information
    'author' => array(
        'name'    => array('Eyrah Temet'),
        'alias'   => array('Eyraahh'),
        'email'   => array('eyrah.temet@theamus.com'),
        'company' => 'Theamus'
    )
));