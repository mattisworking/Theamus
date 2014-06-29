<?php

// Check the user's permissions for editing accounts
if ($tUser->has_permission('edit_users') == false) {
    die(alert_notify('danger', 'You don\'t have permission to edit users.'));
}

// Check for a valid account ID
if (!isset($url_params[0]) || !is_numeric($url_params[0])) {
    die(alert_notify('danger', 'Error finding the user from the given (or not?) ID.'));
}

// Query the database for the user based on the given ID
$user_query = $tData->select_from_table($tData->prefix.'users',
                                        array(),
                                        array('operator'    => '',
                                              'conditions'  => array('selector' => $url_params[0])),
                                        'ORDER BY `id`');

// Check the user query
if ($user_query == false) {
    die(alert_notify('danger', 'There was an issue when querying for users in the database.'));
}

// Check for any returned results from the database
if ($tData->count_rows($user_query) == 0) {
    die(alert_notify('danger', 'This user was not found in the database.'));
}

// Define the user data from the gathered information
$user_results = $tData->fetch_rows($user_query);

// Assign the user data into an associative array to make it easier for data grabbing
$user = array();
foreach ($user_results as $result) {
    $user[$result['key']] = $result['value'];
}

// Split up the birthday for the selects
$user['birthday_array'] = explode('-', $user['birthday']);

?>

<!-- Accounts Tabs -->
<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<!-- Form Results -->
<div id='edit-account-result'></div>

<!-- New Account Form -->
<form class='form-horizontal edit-account-form'>
    <div class='form-header'>Login Information</div>

    <!-- Username -->
    <div class='form-group'>
        <label class='control-label col-3'>Username</label>
        <div class='col-9'>
            <input type='text' class='form-control' disabled='disabled' autocomplete='off' value='<?php echo $user['username']; ?>'>
        </div>
        <div class='form-control-feedback col-offset-3'>
            This is the user's username.  It cannot be changed once the account has been created.
        </div>
    </div>

    <hr class='form-split' />

    <!-- Password Change -->
    <div class='form-group'>
        <label class='checkbox'>
            <input type='checkbox' name='change_password'>
            Change password
        </label>
    </div>

    <div id='password-group' style='display:none;'>
        <!-- Password -->
        <div class='form-group'>
            <label class='control-label col-3' for='password'>Password</label>
            <div class='col-9'>
                <input type='password' class='form-control' name='password' id='password'>
            </div>
        </div>

        <!-- Repeat Password -->
        <div class='form-group'>
            <label class='control-label col-3' for='password-again'>Password Again</label>
            <div class='col-9'>
                <input type='password' class='form-control' name='password_again' id='password-again'>
            </div>
        </div>
    </div>

    <div class='form-header'>Personal Information</div>

    <!-- First Name -->
    <div class='form-group'>
        <label class='control-label col-3' for='firstname'>First Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='firstname' id='firstname' autocomplete='off' value='<?php echo $user['firstname']; ?>'>
        </div>
    </div>

    <!-- Last Name -->
    <div class='form-group'>
        <label class='control-label col-3' for='lastname'>Last Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='lastname' id='lastname' autocomplete='off' value='<?php echo $user['lastname']; ?>'>
        </div>
    </div>

    <!-- Gender -->
    <div class='form-group'>
        <label class='control-label col-3' for='gender'>Gender</label>
        <div class='col-9'>
            <select class='form-control' name='gender' id='gender'>
                <option value='m' <?php if ($user['gender'] == 'm') echo 'selected'; ?>>Male</option>
                <option value='f' <?php if ($user['gender'] == 'f') echo 'selected'; ?>>Female</option>
            </select>
        </div>
    </div>

    <!-- Birthday -->
    <div class='form-group'>
        <label class='control-label col-3'>Birthday</label>
        <div class='col-9'>
            <select class='form-control form-control-inline' name='bday_month'>
                <?php
                for ($i=1; $i<=12; $i++) {
                    $selected = $user['birthday_array'][1] == $i ? 'selected' : '';
                    echo '<option value=\''.$i.'\' '.$selected.'>'.date('F', strtotime('2000-'.$i.'-1')).'</option>';
                }
                ?>
            </select> /
            <select class='form-control form-control-inline' name='bday_day'>
                <?php
                for ($i=1; $i<=31; $i++) {
                    $selected = $user['birthday_array'][2] == $i ? 'selected' : '';
                    echo '<option value=\''.$i.'\' '.$selected.'>'.$i.'</option>';
                }
                ?>
            </select> /
            <select class='form-control form-control-inline' name='bday_year'>
                <?php
                for ($i=2014; $i>=1940; $i--) {
                    $selected = $user['birthday_array'][0] == $i ? 'selected' : '';
                    echo '<option value=\''.$i.'\' '.$selected.'>'.$i.'</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <div class='form-header'>Contact Information</div>

    <!-- Email -->
    <div class='form-group'>
        <label class='control-label col-3' for='email'>Email</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='email' id='email' autocomplete='off' value='<?php echo $user['email']; ?>'>
        </div>
    </div>

    <!-- Phone Number -->
    <div class='form-group'>
        <label class='control-label col-3' for='phone'>Phone</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='phone' id='phone' autocomplete='off' value='<?php echo $user['phone']; ?>'>
        </div>
    </div>

    <?php if ($tUser->is_admin() && $tUser->in_group('administrators')): ?>
    <div class='form-header'>Permissions and Access</div>

    <!-- Groups -->
    <div class='form-group'>
        <label class='control-label col-3' for='groups'>Groups</label>
        <div class='col-9'>
            <select name='groups' multiple='multiple' size='7'>
                <?php
                $query = $tData->select_from_table($tData->prefix.'groups', array('alias', 'name'));

                $results = $tData->fetch_rows($query);
                foreach ($results as $group) {
                    $selected = $group['alias'] == 'everyone' || strstr($user['groups'], $group['alias']) !== false ? 'selected' : '';
                    echo '<option '.$selected.' value=\''.$group['alias'].'\'>'.$group['name'].'</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Administrator -->
    <div class='form-group'>
        <label class='checkbox'>
            <input type='checkbox' name='is_admin' <?php if ($user['admin'] == 1) echo 'checked'; ?>>
            Administrator
        </label>
        <p class='form-control-feedback'>
            <strong>Note:</strong> Making the user an administrator will give he or she the rights to the administration panel.  This does not affect the abilities and access provided by placing the user in the 'Administrators' group, however.
        </p>
    </div>
    <?php endif; ?>

    <!-- Active User -->
    <div class='form-group'>
        <label class='checkbox'>
            <input type='checkbox' name='active' <?php if ($user['active'] == 1) echo 'checked'; ?>>
            Active User
        </label>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Save Information</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('change_accounts_tab'); // Listen to the tab changer

        // Toggle the password change fields
        $('[name="change_password"]').click(function(e) {
            if (this.checked === true) {
                $('#password-group').show();
            } else {
                $('#password-group').hide();
            }
        });

        // Save account information form submission
        $('.edit-account-form').submit(function(e) {
            e.preventDefault();

            // Scroll to the top of the window and show a loading notification
            $('#theamus-accounts').scrollTop(0);
            $('#edit-account-result').html(alert_notify('spinner', 'Saving...'));

            // Make the call to save this user information
            theamus.ajax.api({
                type:       'post',
                url:        theamus.base_url+'accounts/admin/save-account',
                method:     ['Accounts', 'save_account_information'],
                data:       {
                    form: this,
                    custom: { id: encode('<?php echo $user['id']; ?>') }
                },
                success:    function(data) {
                    // Show an error if the call returned isn't what it should be
                    if (typeof(data) !== 'object') {
                        $('#edit-account-result').html(alert_notify('danger', 'Something happened when trying to save this information. It didn\'t work. :('));
                        return;
                    }

                    // Show the error produced by the call
                    if (typeof(data.response.data) !== 'boolean') {
                        $('#edit-account-result').html(data.response.data);
                        return;
                    }

                    // Show the success message for a successful result
                    $('#edit-account-result').html(alert_notify('success', 'This information has been saved.'));
                }
            });
        });
    });
</script>