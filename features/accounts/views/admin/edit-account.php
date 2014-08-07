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
if ($Theamus->DB->count_rows($user_query) == 0) die($Theamus->notify('danger', 'Failed to find user'));

// Define the user data from the gathered information
$user = $Theamus->DB->fetch_rows($user_query);

// Split up the birthday for the selects
$user['birthday_array'] = explode('-', $user['birthday']);

?>

<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<div id='edit-account-result'></div>

<form class='form-horizontal edit-account-form'>
    <h3 class='form-header'>Login Information</h3>

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

    <div class='form-group'>
        <label class='checkbox'>
            <input type='checkbox' name='change_password'>
            Change password
        </label>
    </div>

    <div id='password-group' style='display:none;'>
        <div class='form-group'>
            <label class='control-label col-3' for='password'>Password</label>
            <div class='col-9'>
                <input type='password' class='form-control' name='password' id='password'>
            </div>
        </div>

         <div class='form-group'>
            <label class='control-label col-3' for='password-again'>Password Again</label>
            <div class='col-9'>
                <input type='password' class='form-control' name='password_again' id='password-again'>
            </div>
        </div>
    </div>

    <h3 class='form-header'>Personal Information</h3>

    <div class='form-group'>
        <label class='control-label col-3' for='firstname'>First Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='firstname' id='firstname' autocomplete='off' value='<?php echo $user['firstname']; ?>'>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='lastname'>Last Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='lastname' id='lastname' autocomplete='off' value='<?php echo $user['lastname']; ?>'>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='gender'>Gender</label>
        <div class='col-9'>
            <select class='form-control' name='gender' id='gender'>
                <option value='m' <?php if ($user['gender'] == 'm') echo 'selected'; ?>>Male</option>
                <option value='f' <?php if ($user['gender'] == 'f') echo 'selected'; ?>>Female</option>
            </select>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3'>Birthday</label>
        <div class='col-9'>
            <select class='form-control form-control-inline' name='bday_month'>
                <?php
                for ($i=1; $i<=12; $i++) {
                    $selected = $user['birthday_array'][1] == $i ? 'selected' : '';
                    echo '<option value="'.$i.'" '.$selected.'>'.date('F', strtotime('2000-'.$i.'-1')).'</option>';
                }
                ?>
            </select> /
            <select class='form-control form-control-inline' name='bday_day'>
                <?php
                for ($i=1; $i<=31; $i++) {
                    $selected = $user['birthday_array'][2] == $i ? 'selected' : '';
                    echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                }
                ?>
            </select> /
            <select class='form-control form-control-inline' name='bday_year'>
                <?php
                for ($i=2014; $i>=1940; $i--) {
                    $selected = $user['birthday_array'][0] == $i ? 'selected' : '';
                    echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <h3 class='form-header'>Contact Information</h3>

    <div class='form-group'>
        <label class='control-label col-3' for='email'>Email</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='email' id='email' autocomplete='off' value='<?php echo $user['email']; ?>'>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='phone'>Phone</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='phone' id='phone' autocomplete='off' value='<?php echo $user['phone']; ?>'>
        </div>
    </div>

    <?php if ($Theamus->User->is_admin() && $Theamus->User->in_group('administrators')): ?>
    <h3 class='form-header'>Permissions and Access</h3>

    <div class='form-group'>
        <label class='control-label col-3' for='groups'>Groups</label>
        <div class='col-9'>
            <select class='form-control' name='groups' multiple='multiple' size='7'>
                <?php
                // Query the database for groups
                $query = $Theamus->DB->select_from_table(
                    $Theamus->DB->system_table('groups'),
                    array('alias', 'name'));

                // Check the query for errors
                if (!$query) {
                    $Theamus->Log->query($Theamus->DB->get_last_error()); // Log the query error
                    echo '<option>Failed to find groups.</option>';
                } else {
                    // Define the groups from the query
                    $results = $Theamus->DB->fetch_rows($query);

                    // Loop throug all of the groups
                    foreach (isset($results[0]) ? $results : array($results) as $group) {
                        $selected = $group['alias'] == 'everyone' || strstr($user['groups'], $group['alias']) !== false ? 'selected' : '';
                        echo '<option '.$selected.' value="'.$group['alias'].'">'.$group['name'].'</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>

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

    <input type='hidden' name='id' value='<?php echo $user['id'];?>'>
</form>

<script>
    admin_window_run_on_load('change_accounts_tab');
    admin_window_run_on_load('edit_account');
</script>