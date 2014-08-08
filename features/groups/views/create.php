<div class='admin-tabs'><?php echo $Groups->groups_tabs(FILE); ?></div>

<div id='group-result' style='margin-top: 15px;'></div>

<form class='form-horizontal' id='create-group-form' style='margin-top: 15px; width: 700px;'>
    <div class='form-group'>
        <label class='control-label col-3' for='name'>Group Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='name' id='name' autocomplete='off'>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='permissions'>Permissions</label>
        <div class='col-9'>
            <select class='form-control' name='permissions' id='permissions' size='20' multiple='multiple'>
                <?php
                // Query the database for permissions
                $query = $Theamus->DB->select_from_table(
                        $Theamus->DB->system_table('permissions'),
                        array('permission', 'feature'));
                
                // Check the query for errors
                if (!$query) {
                    $Theamus->Log->query($Theamus->DB->get_last_error()); // Log the query error
                    
                    echo '<option>Failed to get permissions.</option>';
                } else {
                    // Define the permissions
                    $results = $Theamus->DB->fetch_rows($query);
                    
                    // Loop through all of the permissions
                    foreach (isset($results[0]) ? $results : array($results) as $permission) {
                        // Clean up the text for the permission and the feature it belongs to
                        $permission_permission  = ucwords(str_replace('_', ' ', $permission['permission']));
                        $permission_feature     = ucwords(str_replace('_', ' ', $permission['feature']));

                        // Check for the user's permission/group level to prevent them from adding permissions thy don't already have
                        if ($Theamus->User->has_permission($permission['permission']) || ($Theamus->User->is_admin() && $Theamus->User->in_group('administrators'))) {
                            echo '<option value="'.$permission['permission'].'">'.$permission_feature.' - '.$permission_permission.'</option>';
                        }
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
    admin_window_run_on_load('create_group');
</script>