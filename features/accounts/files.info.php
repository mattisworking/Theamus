<?php

if ($folders[0] == 'admin' && $ajax != 'api' && $ajax != 'include' && $Theamus->User->is_admin() == false) {
    if ($location != 'admin') back_up();
    if ($location != 'admin' && $ajax != 'include') die('You don\'t have permission to this file.');
}

$feature['class']['file'] = 'accounts.class.php';
$feature['class']['init'] = 'Accounts';

define('FILE', $file);

switch ($file) {
    // Public files
    case 'login.php' :
        if ($Theamus->User->user) back_up();
        $feature['title'] = 'Log In';
        $feature['header'] = 'Log In';
        $feature['theme'] = 'login';
        break;

    case 'register.php':
        if ($Theamus->User->user) back_up();
        $feature['title'] = 'Register';
        $feature['header'] = 'Register';
        $feature['theme'] = 'register';
        break;

    case 'activate.php':
        if ($Theamus->User->user) back_up();
        $feature['title'] = 'Activate Your Account';
        $feature['header'] = 'Activate Your Account';
        break;

    // User files
    case 'user/index.php':
        back_up();
        break;

    case 'user/edit-account.php':
    case 'user/edit-personal.php':
    case 'user/edit-contact.php':
    case 'user/other-information.php':
        if (!$Theamus->User->user) { back_up(); }
        $feature['title'] = $feature['header'] = 'Edit Your Account';
        switch ($file) {
            case 'user/edit-account.php':
                $feature['title'] .= ' - Account Information';
                $feature['header'] .= ' - Account Information';
                break;
            case 'user/edit-personal.php':
                $feature['title'] .= ' - Personal Information';
                $feature['header'] .= ' - Personal Information';
                break;
            case 'user/edit-contact.php':
                $feature['title'] .= ' - Contact Information';
                $feature['header'] .= ' - Contact Information';
                break;
            case 'user/other-information.php':
                $feature['title'] = $feature['header'] = 'Other Account Information';
                break;
            default:
                $feature['title'] = 'Edit Your Account';
                $feature['header'] = 'Edit Your Account';
                break;
        }
        $feature['nav'] = array(
            'Account Information' => 'accounts/user/edit-account',
            'Personal Information' => 'accounts/user/edit-personal',
            'Contact Information' => 'accounts/user/edit-contact',
            'Other Information' => 'accounts/user/other-information'
        );

    // Administrator files
    case 'admin/index.php':
    case 'admin/search-accounts.php':
    case 'admin/create-account.php':
    case 'admin/edit-account.php':
    case 'admin/remove-account.php':
        $feature['css']['file'][]   = 'accounts.css';
        $feature['js']['file'][]    = 'accounts.js';
        break;

    default :
        break;
}