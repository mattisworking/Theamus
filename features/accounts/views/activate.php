<?php

// Clean the get information
$get = filter_input_array(INPUT_GET);

// Try to activate the user
try {
    $Accounts->activate_user($get['email'], $get['code']);

    $Theamus->notify('success', 'This account has been activated!  You can log in <a href="accounts/login/">here</a>!');
} catch (Exception $ex) {
    $Theamus->notify('danger', $ex->getMessage());
}