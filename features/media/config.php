<?php

define('MEDIA_DEV_MODE', FALSE);

$Theamus->Call->set_feature_config(array(
    // System Required
    'theamus_version' => '1.3.0',
    'permissions'     => array('database', 'files'),

    // Feature Required
    'folder_name'     => 'media',
    'feature_name'    => 'Theamus Media',
    'feature_version' => '1.3.0',
    'custom_folders'  => array(
        'php'        => 'php',
        'javascript' => 'js',
        'css'        => 'css',
        'class'      => 'php'
    ),

    // Feature Optional
    'load_files' => array(
        'api' => array('php/media.class.php')
    ),
    'release_notes' => array(
        '1.3.0' => array('Update to work with the latest version of Theamus'),
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