<?php

// Error checking
$error = array();
$query_data = array(
    "table" => $tData->prefix."settings",
    "data"  => array()
);

// Config email
$configEmail = filter_var($_POST['config-email'], FILTER_VALIDATE_BOOLEAN);
if ($configEmail == true)
{
// Host
       $_POST['host'] != "" ? $host = urldecode($_POST['host'])
              : $error[] = "Please fill out the 'Host' field.";

       // Protocol
       $protocol = $_POST['protocol'];

       // Port
       $_POST['port'] != "" ? $port = $_POST['port']
              : $error[] = "Please fill out the 'Port' field.";
       if (!is_numeric(@$port)) $error[] = "The port must be a number.";

       // Email
       $_POST['email'] != "" ? $email = urldecode($_POST['email'])
              : $error[] = "Please fill out the 'Email' field.";
       if (!filter_var(@$email, FILTER_VALIDATE_EMAIL)) {
              $error[] = "Please enter a valid email address.";
       }

       // Password
       $_POST['password'] != "" ? $password = urldecode($_POST['password'])
              : $error[] = "Please fill out the 'Password' field.";
}

// Display errors
$displayErrors = filter_var($_POST['errors'], FILTER_VALIDATE_BOOLEAN);

// Show errors
if (!empty($error)) {
	alert_notify("danger", $error[0]);
} else {
	$displayErrors = $displayErrors == true ? "1" : "0";

    $query_data['data'] = array(
        "display_errors"    => $displayErrors
    );

	// Sanitize the variables
	if ($configEmail == true) {
        $query_data['data']['email_host']       = $host;
        $query_data['data']['email_protocol']   = $protocol;
		$query_data['data']['email_port']       = $port;
        $query_data['data']['email_user']       = $email;
        $query_data['data']['email_password']   = $password;
	}

	// Run query
	$query = $tData->update_table_row($query_data['table'], $query_data['data'], array(
        "operator"  => "",
        "conditions"=> array("id" => 1)
    ));

    if ($query != false) {
        alert_notify("success", "These settings have been saved!");
    } else {
        alert_notify("danger", "There was an error saving this information.");
    }
}


?>