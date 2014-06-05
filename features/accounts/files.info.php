<?php

if ($folders[0] == 'admin' && $ajax != 'api' && $ajax != 'include' && $tUser->is_admin() == false) {
    if ($location != 'admin') back_up();
    if ($location != 'admin' && $ajax != 'include') die('You don\'t have permission to this file.');
}

$feature['class']['file'] = 'accounts.class.php';
$feature['class']['init'] = 'Accounts';

define('FILE', $file);

// Customize files
switch ($file) {
    case 'admin/index.php':
    case 'admin/search-accounts.php':
    case 'admin/create-new-accout.php':
        $feature['css']['file'][]   = 'accounts.css';
        $feature['js']['file'][]    = 'accounts.js';
        break;

    case 'edit.php':
        $tUser->check_permissions('edit_users');
        $feature['js']['file'][] = 'edit_user.js';
        break;

    case 'login.php' :
        if ($tUser->user) back_up();
        $feature['title'] = 'Login';
        $feature['header'] = 'Log In';
        $feature['js']['file'][] = 'sessions.js';
        $feature['theme'] = 'login';
        break;

    case 'register.php':
        if ($tUser->user) back_up();
        $feature['title'] = 'Register';
        $feature['header'] = 'Register';
        $feature['js']['file'][] = 'sessions.js';
        $feature['theme'] = 'register';
        break;

    case 'user/index.php':
        back_up();
        break;

    case 'remove.php':
        $tUser->check_permissions('remove_users');
        break;

    case 'activate.php':
        $feature['title'] = 'Activate Your Account';
        $feature['header'] = 'Activate Your Account';
        break;

    case 'user/edit-account.php':
    case 'user/edit-personal.php':
    case 'user/edit-contact.php':
    case 'user/other-information.php':
        if (!$tUser->user) { back_up(); }
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
        $feature['js']['file'][] = 'edit_user.js';
        $feature['nav'] = array(
            'Account Information' => 'accounts/user/edit-account',
            'Personal Information' => 'accounts/user/edit-personal',
            'Contact Information' => 'accounts/user/edit-contact',
            'Other Information' => 'accounts/user/other-information'
        );

    default :
        break;
}