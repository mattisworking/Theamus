<?php

define('FEATURES_DEV_MODE', FALSE);

$Theamus->Call->set_feature_config(array(
    // System Required
    'theamus_version' => '1.3.0',
    'permissions'     => array('database', 'files'),

    // Feature Required
    'folder_name'     => 'features',
    'feature_name'    => 'Theamus Features',
    'feature_version' => '1.3.1',
    'custom_folders'  => array(
        'php'        => 'php',
        'javascript' => 'js',
        'css'        => 'css',
        'class'      => 'php'
    ),

    // Feature Optional
    'load_files' => array(
        'api'       => array('php/features.class.php'),
    ),
    'release_notes' => array(
        '1.3.1' => array('Fixed the versions listing bug. https://github.com/aelieo/Theamus/issues/78'),
        '1.3.0' => array('Updated to the new way of doing things.'),
        '1.0' => array('Initial release.')
    ),

    // Optional Author Information
    'author' => array(
        'name'    => array('ælieo'),
        'alias'   => array('aelieo'),
        'email'   => array('aelieo@theamus.com'),
        'company' => 'Theamus'
    )
));