<?php

// Clean the get information
$get = filter_input_array(INPUT_GET);


// Perform the api call and define the return information
$api = $tData->api(array(
    'type'  => 'post',
    'url'   => 'accounts/activate/',
    'method'=> array('AccountsApi', 'activate_user'),
    'data'  => array(
        'email' => isset($get['email']) ? $get['email'] : '',
        'code'  => isset($get['code']) ? $get['code'] : ''
    )
));

// Redirect the user if there are any unknown results or they have already activated their account
if ($api['response']['data'] == 'active') {
    header('Location: '.base_url.'accounts/login/');
}

// Notify the user related to the results
if ($api['response']['data']['error'] == true) {
    alert_notify('danger', $api['response']['data']['message']);
    header('Refresh:5; '.base_url);
} elseif ($api['response']['data']['error'] == false) {
    alert_notify('success', $api['response']['data']['message']);
}