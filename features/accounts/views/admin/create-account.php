<?php

// Check the user's permission against creating accounts
if (!$Theamus->User->has_permission('add_users')) die('You don\'t have permission to create new users.');

?>

<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<div id='create-account-result'></div>

<form class='form-horizontal new-account-form'>
    <h3 class='form-header'>Login Information</h3>

    <div class='form-group'>
        <label class='control-label col-3' for='username'>Username</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='username' id='username' autocomplete='off'>
        </div>
        <div class='form-control-feedback col-offset-3'>
            This is the user's username.  It cannot be changed once the account has been created.
        </div>
    </div>

    <hr class='form-split' />

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

    <h3 class='form-header'>Personal Information</h3>

    <div class='form-group'>
        <label class='control-label col-3' for='firstname'>First Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='firstname' id='firstname' autocomplete='off'>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='lastname'>Last Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='lastname' id='lastname' autocomplete='off'>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='gender'>Gender</label>
        <div class='col-9'>
            <select class='form-control' name='gender' id='gender'>
                <option value='m'>Male</option>
                <option value='f'>Female</option>
            </select>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3'>Birthday</label>
        <div class='col-9'>
            <select class='form-control form-control-inline' name='bday_month'>
                <?php
                for ($i=1; $i<=12; $i++) {
                    echo '<option value="'.$i.'">'.date('F', strtotime('2000-'.$i.'-1')).'</option>';
                }
                ?>
            </select> /
            <select class='form-control form-control-inline' name='bday_day'>
                <?php
                for ($i=1; $i<=31; $i++) {
                    echo '<option value="'.$i.'">'.$i.'</option>';
                }
                ?>
            </select> /
            <select class='form-control form-control-inline' name='bday_year'>
                <?php
                for ($i=2014; $i>=1940; $i--) {
                    echo '<option value="'.$i.'">'.$i.'</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <h3 class='form-header'>Contact Information</h3>

    <div class='form-group'>
        <label class='control-label col-3' for='email'>Email</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='email' id='email' autocomplete='off'>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='phone'>Phone</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='phone' id='phone' autocomplete='off'>
        </div>
    </div>

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
                        // Define the selected groups
                        $selected = $group['alias'] == 'everyone' ? 'selected' : '';

                        // Check the user permissions to restrict from adding to groups they aren't in
                        if ($Theamus->User->in_group($group['alias']) || ($Theamus->User->is_admin() && $Theamus->User->in_group('administrators'))) {
                            echo '<option '.$selected.' value="'.$group['alias'].'">'.$group['name'].'</option>';
                        }
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <?php if ($Theamus->User->is_admin() && $Theamus->User->in_group('administrators')): ?>
    <div class='form-group'>
        <label class='checkbox' for='is-admin'>
            <input type='checkbox' name='is_admin' id='is-admin'>
            Administrator
        </label>
        <div class='form-control-feedback'>
            <br><strong>Note:</strong> Making the user an administrator will give he or she the rights to the administration panel.  This does not affect the abilities and access provided by placing the user in the 'Administrators' group, however.
        </div>
    </div>
    <?php endif; ?>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Create User</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_accounts_tab');
    admin_window_run_on_load('create_account');
</script>