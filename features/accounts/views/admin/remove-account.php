<?php

// Check the user's permissions for editing accounts
if (!$Theamus->User->has_permission('edit_users')) die($Theamus->notify('danger', 'You don\'t have permission to edit users.'));

// Check for a valid account ID
if (!isset($Theamus->Call->parameters[0]) || !is_numeric($Theamus->Call->parameters[0])) die($Theamus->notify('danger', 'Error finding the user from the given (or not?) ID.'));

// Query the database for the user based on the given ID
$user_query = $Theamus->DB->select_from_table(
    $Theamus->DB->system_table('users'),
    array(),
    array('operator' => '',
        'conditions' => array('id' => $Theamus->Call->parameters[0])));

// Check the user query
if (!$user_query) {
    $Theamus->Log->query($Theamus->DB->get_last_error()); // Log the query error

    // Notify the user
    die($Theamus->notify('danger', 'Failed to find user.'));
}

// Check for any returned results from the database
if ($Theamus->DB->count_rows($user_query) == 0) die($Theamus->notify('danger', 'Failed to find user.'));

// Define the user data from the gathered information
$user = $Theamus->DB->fetch_rows($user_query);


?>

<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<div id='remove-account-result'></div>

<form class='form remove-account-form'>
    <div class='form-group'>
        <?php if ($user['permanent'] == 0): ?>
        Are you sure you want to remove <strong><?php echo $user['firstname'].' '.$user['lastname']; ?></strong> <i>(<?php echo $user['username']; ?>)</i>?
        <?php else:
            alert_notify('info', 'This is a permanent account and cannot be deleted. - <a href=\'#\' name=\'cancel\'>Go Back</a>');
        endif; ?>
    </div>

    <div class='form-button-group'>
        <?php if ($user['permanent'] == 0): ?>
        <button type='button' class='btn btn-default' name='cancel'>Cancel</button>
        <button type='submit' class='btn btn-danger'>Remove User</button>
        <?php endif; ?>
    </div>

    <input type='hidden' id='id' value='<?php echo $user['id'];?>'>
</form>

<script>
    admin_window_run_on_load('change_accounts_tab');
    admin_window_run_on_load('remove_account');
</script>