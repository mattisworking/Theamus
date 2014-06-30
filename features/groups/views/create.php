<!-- Groups Tabs -->
<div class='admin-tabs'><?php echo $Groups->groups_tabs(FILE); ?></div>

<!-- Form Results -->
<div id='group-result'></div>

<!-- Form -->
<form class='form-horizontal' id='group-form' onsubmit='return create_group();' style='margin-top: 20px;'>
    <!-- Group Name -->
    <div class='form-group'>
        <label class='control-label col-3' for='name'>Group Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='name' id='name' autocomplete='off'>
        </div>
    </div>

    <!-- Permissions -->
    <div class='form-group'>
        <label class='control-label col-3' for='permissions'>Permissions</label>
        <div class='col-9'>
            <select class='form-control' name='permissions' id='permissions' size='20' multiple='multiple'>
                <?php
                // Query the database for permissions
                $query = $tData->select_from_table($tData->prefix.'permissions', array('permission', 'feature'));

                // Loop through results
                $results = $tData->fetch_rows($query);
                foreach ($results as $permission) {
                    // Clean up the text
                    $permission_permission  = ucwords(str_replace('_', ' ', $permission['permission']));
                    $permission_feature     = ucwords(str_replace('_', ' ', $permission['feature']));

                    // Show options
                    if ($tUser->has_permission($permission['permission']) || ($tUser->is_admin() && $tUser->in_group('administrators'))) {
                        echo '<option value=\''.$permission['permission'].'\'>'.$permission_feature.' - '.$permission_permission.'</option>';
                    }
                }
                ?>
            </select>
            <p class='form-control-feedback'>All of the permissions selected here will be available to any user in this group.</p>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Create Group</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_groups_tab');
</script>