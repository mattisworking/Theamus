<?php

// Check if the requesting user can remove accounts
if ($tUser->has_permission('remove_users') == false) {
    die(alert_notify('danger', 'You don\'t have permission to remove users.'));
}

// Check for a valid ID
if (!isset($url_params[0]) || !is_numeric($url_params[0])) {
    die(alert_notify('danger', 'Error finding the user from the given (or not?) ID.'));
}

// Find the user information in the database
$user_query = $tData->select_from_table($tData->prefix.'_users', array(), array('operator' => '', 'conditions' => array('selector' => $url_params[0])), 'ORDER BY `id`');

// Check the user query
if ($user_query == false) {
    die(alert_notify('danger', 'There was an issue when querying for users in the database.'));
}

// Check for query rows/results
if ($tData->count_rows($user_query) == 0) {
    die(alert_notify('danger', 'This user was not found in the database.'));
}

// Define all of the user information
$user_results = $tData->fetch_rows($user_query);

// Clean up the user information, taking only what we want
$user = array();
$want = array('id', 'username', 'firstname', 'lastname', 'permanent');
foreach ($user_results as $result) {
    if (in_array($result['key'], $want)) {
        $user[$result['key']] = $result['value'];
    }
}

?>

<!-- Accounts Tabs -->
<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<!-- Form Results -->
<div id='remove-account-result'></div>

<!-- Remove Account Form -->
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
</form>

<script>
    $(document).ready(function() {
        // Go back to the list of people
        $('[name="cancel"]').click(function(e) {
            update_admin_window_content('theamus-accounts', 'accounts/admin/');
            change_admin_window_title('theamus-accounts', 'Theamus Accounts');
        });

        // Remove account form submission
        $('.remove-account-form').submit(function(e) {
            e.preventDefault();

            // Scroll to the top of the window and show a loading notification
            $('#theamus-accounts').scrollTop(0);
            $('#remove-account-result').html(alert_notify('spinner', 'Removing...'));

            // Make the call to remove the account
            theamus.ajax.api({
                type:       'post',
                url:        theamus.base_url+'accounts/admin/remove-account/',
                method:     ['Accounts', 'remove_user_account'],
                data:       { custom: { id: encode('<?php echo $user['id']; ?>') } },
                success:    function(data) {
                    // Show an error if the call returned isn't what it should be
                    if (typeof(data) !== 'object') {
                        $('#remove-account-result').html(alert_notify('danger', 'Something happened when trying to remove this account. It didn\'t work. :('));
                        return;
                    }

                    // Show the error produced by the call
                    if (typeof(data.response.data) !== 'boolean') {
                        $('#remove-account-result').html(data.response.data);
                        return;
                    }

                    // Show the success message for a successful result
                    $('#remove-account-result').html(alert_notify('success', 'This user has been deleted.'));

                    // Go back to the list of users
                    setTimeout(function() {
                        update_admin_window_content('theamus-accounts', 'accounts/admin/');
                        change_admin_window_title('theamus-accounts', 'Theamus Accounts');
                    }, 1500);
                }
            });
        });
    });
</script>