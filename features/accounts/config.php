<?php

define('ACCOUNTS_DEV_MODE', FALSE);

$Theamus->Call->set_feature_config(array(
    // System Required
    'theamus_version' => '1.3.0',
    'permissions'     => array('database'),

    // Feature Required
    'folder_name'     => 'accounts',
    'feature_name'    => 'Theamus Accounts',
    'feature_version' => '1.3.0',
    'custom_folders'  => array(
        'php'        => 'php',
        'javascript' => 'js',
        'css'        => 'css',
        'class'      => 'php'
    ),

    // Feature Optional
    'load_files' => array(
        'api' => array('php/accounts.class.php')
    ),
    'release_notes' => array(
        '1.3.0' => array('Update to work with the latest version of Theamus'),
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