<?php

// Check for a call to the administrator and for an administrator
if (end($folders) == 'admin' && $ajax == false) $Theamus->back_up();
if (end($folders) == "admin" && !$Theamus->User->is_admin()) die("You don't have permission to this feature.");

// Load the accounts class
$feature['class']['file'] = 'accounts.class.php';
$feature['class']['init'] = 'Accounts';

define('FILE', "accounts/{$file}"); // Define the current file

// Load the file related information
switch ($file) {
    case 'index.php':
        if ($ajax == false) $Theamus->back_up();
        break;

    case 'login.php' :
        if ($Theamus->User->user) $Theamus->back_up();
        $feature['title']  = 'Log In';
        $feature['header'] = 'Log In';

        $feature['js']['file'][]    = ACCOUNTS_DEV_MODE ? 'dev/accounts.js' : 'accounts.min.js';

        $feature['theme']  = 'login';
        break;

    case 'register.php':
        if ($Theamus->User->user) $Theamus->back_up();
        $feature['title']  = 'Register';
        $feature['header'] = 'Register';

        $feature['js']['file'][]    = ACCOUNTS_DEV_MODE ? 'dev/accounts.js' : 'accounts.min.js';

        $feature['theme']  = 'register';
        break;

    case 'activate.php':
        if ($Theamus->User->user) $Theamus->back_up();
        $feature['title']  = 'Activate Your Account';
        $feature['header'] = 'Activate Your Account';
        break;


    case 'user/index.php':
        $Theamus->back_up(); break;
    case 'user/edit-account.php':
    case 'user/edit-personal.php':
    case 'user/edit-contact.php':
    case 'user/other-information.php':
        if (!$Theamus->User->user) !$ajax ? $Theamus->back_up() : die();

        $feature['js']['file'][]    = ACCOUNTS_DEV_MODE ? 'dev/accounts.js' : 'accounts.min.js';

        $feature['title'] = $feature['header'] = 'Edit Your Account';

        switch ($file) {
            case 'user/edit-account.php':
                $feature['title']  .= ' - Account Information';
                $feature['header'] .= ' - Account Information';
                break;
            case 'user/edit-personal.php':
                $feature['title']  .= ' - Personal Information';
                $feature['header'] .= ' - Personal Information';
                break;
            case 'user/edit-contact.php':
                $feature['title']  .= ' - Contact Information';
                $feature['header'] .= ' - Contact Information';
                break;
            case 'user/other-information.php':
                $feature['title'] = $feature['header'] = 'Other Account Information';
                break;
        }

        $feature['nav'] = array(
            'Account Information'  => 'accounts/user/edit-account',
            'Personal Information' => 'accounts/user/edit-personal',
            'Contact Information'  => 'accounts/user/edit-contact',
            'Other Information'    => 'accounts/user/other-information');
        break;


    case 'admin/index.php':
    case 'admin/search-accounts.php':
    case 'admin/create-account.php':
    case 'admin/edit-account.php':
    case 'admin/remove-account.php':
        // Don't do anything if the user is not an administrator;
        if (!$Theamus->User->is_admin()) !$ajax ? $Theamus->back_up() : die();
        $feature['css']['file'][]   = ACCOUNTS_DEV_MODE ? 'dev/accounts.css' : 'accounts.min.css';
        $feature['js']['file'][]    = ACCOUNTS_DEV_MODE ? 'dev/accounts.admin.js' : 'accounts.admin.min.js';
        break;
}