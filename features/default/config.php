<?php

define('DFLT_DEV_MODE', FALSE);

$Theamus->Call->set_feature_config(array(
    // System Required
    'theamus_version' => '1.3.0',
    'permissions'     => array('database', 'files'),

    // Feature Required
    'folder_name'     => 'default',
    'feature_name'    => 'Default',
    'feature_version' => '1.3.0',
    'custom_folders'  => array(
        'php'        => 'php',
        'javascript' => 'js',
        'css'        => 'css',
        'class'      => 'php'
    ),

    // Feature Optional
    'load_files' => array(
        'function'  => array('php/homepage.class.php'),
        'api'       => array('php/admin.class.php'),
    ),
    'release_notes' => array(
        '1.3.0' => array('Updated to the new way of doing things.'),
        '1.0' => array('Initial release.')
    ),

    // Optional Author Information
    'author' => array(
        'name'    => array('MMT'),
        'alias'   => array('helllomatt'),
        'email'   => array('mmt@itsfake.com'),
        'company' => 'Theamus'
    )
));