<?php

if ($tUser->has_permission('remove_users') == false) {
    die(alert_notify('danger', 'You don\'t have permission to remove users.'));
}

if (!isset($url_params[0]) || !is_numeric($url_params[0])) {
    die(alert_notify('danger', 'Error finding the user from the given (or not?) ID.'));
}

$user_id = $url_params[0];
$user_query = $tData->select_from_table($tData->prefix.'_users', array(), array('operator' => '', 'conditions' => array('selector' => $user_id)), 'ORDER BY `id`');
if ($user_query == false) {
    die(alert_notify('danger', 'There was an issue when querying for users in the database.'));
}

if ($tData->count_rows($user_query) == 0) {
    die(alert_notify('danger', 'This user was not found in the database.'));
}

$user_results = $tData->fetch_rows($user_query);

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
        $('[name="cancel"]').click(function(e) {
            update_admin_window_content('theamus-accounts', 'accounts/admin/');
            change_admin_window_title('theamus-accounts', 'Theamus Accounts');
        });

        $('.remove-account-form').submit(function(e) {
            e.preventDefault();

            $('#theamus-accounts').scrollTop(0);
            $('#remove-account-result').html(alert_notify('spinner', 'Removing...'));

            theamus.ajax.api({
                type:       'post',
                url:        theamus.base_url+'accounts/admin/remove-account/',
                method:     ['AccountsApi', 'remove_user_account'],
                data:       { custom: { id: encode('<?php echo $user['id']; ?>') } },
                success:    function(data) {
                    console.log(data);

                    if (typeof(data) !== 'object') {
                        $('#remove-account-result').html(alert_notify('danger', 'Something happened when trying to remove this account. It didn\'t work. :('));
                        return;
                    }

                    if (typeof(data.response.data) !== 'boolean') {
                        $('#remove-account-result').html(data.response.data);
                        return;
                    }

                    $('#remove-account-result').html(alert_notify('success', 'This user has been deleted.'));
                    setTimeout(function() {
                        update_admin_window_content('theamus-accounts', 'accounts/admin/');
                        change_admin_window_title('theamus-accounts', 'Theamus Accounts');
                    }, 1500);
                }
            });
        });
    });
</script>