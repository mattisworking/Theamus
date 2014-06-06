<?php

if ($tUser->has_permission('add_users') == false) {
    die('You don\'t have permission to create new users.');
}

?>

<!-- Accounts Tabs -->
<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<!-- Form Results -->
<div id="create-account-result"></div>

<!-- New Account Form -->
<form class="form-horizontal new-account-form">
    <div class="form-header">Login Information</div>

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

    <div class='form-header'>Personal Information</div>

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
                    echo "<option value='".$i."'>".date('F', strtotime('2000-'.$i.'-1'))."</option>";
                }
                ?>
            </select> /
            <select class='form-control form-control-inline' name='bday_day'>
                <?php
                for ($i=1; $i<=31; $i++) {
                    echo "<option value='".$i."'>".$i."</option>";
                }
                ?>
            </select> /
            <select class='form-control form-control-inline' name='bday_year'>
                <?php
                for ($i=2014; $i>=1940; $i--) {
                    echo "<option value='".$i."'>".$i."</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <div class='form-header'>Contact Information</div>

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

    <div class='form-header'>Permissions and Access</div>

    <div class='form-group'>
        <label class='control-label col-3' for='groups'>Groups</label>
        <div class='col-9'>
            <select name="groups" multiple="multiple" size="7">
                <?php
                $query = $tData->select_from_table($tData->prefix."_groups", array("alias", "name"));

                $results = $tData->fetch_rows($query);
                foreach ($results as $group) {
                    $selected = $group['alias'] == "everyone" ? "selected" : "";
                    if ($tUser->in_group($group['alias']) || ($tUser->is_admin() && $tUser->in_group("administrators"))) {
                        echo "<option ".$selected." value=\"".$group['alias']."\">".$group['name']."</option>";
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <?php if ($tUser->is_admin() && $tUser->in_group("administrators")): ?>
    <div class="form-group">
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

    <div class="form-button-group">
        <button type='submit' class='btn btn-success'>Create User</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('change_accounts_tab');

        $('.new-account-form').submit(function(e) {
            e.preventDefault();

            $('#theamus-accounts').scrollTop(0);
            $('#create-account-result').html(alert_notify('spinner', 'Creating...'));

            theamus.ajax.api({
                type:       'post',
                url:        theamus.base_url+'accounts/admin/create-account/',
                method:     ['AccountsApi', 'create_new_account'],
                data:       { form: $(this) },
                success:    function(data) {
                    console.log(data);

                    if (typeof(data) !== 'object') {
                        $('#create-account-result').html(alert_notify('danger', 'Something happened when trying to create this user. It didn\'t work. :('));
                        return;
                    }

                    if (typeof(data.response.data) !== 'boolean') {
                        $('#create-account-result').html(data.response.data);
                        return;
                    }

                    $('#create-account-result').html(alert_notify('success', 'This user was created successfully.'));
                    setTimeout(function() {
                        update_admin_window_content('theamus-accounts', 'accounts/admin/');
                        change_admin_window_title('theamus-accounts', 'Theamus Accounts');
                    }, 1500);
                }
            });
        });
    });
</script>