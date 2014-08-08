<?php

// Define and chec the group ID
$id = filter_input(INPUT_GET, 'id');
if ($id == '') die($Theamus->notify('danger', 'Failed to find the ID.'));

// Try to get the group information
try { $group = $Groups->get_group($id); }
catch (Exception $ex) { die($Theamus->notify('danger', $ex->get_message())); }

// Query the database for the users that will be affected by this
$query = $Theamus->DB->select_from_table(
        $Theamus->DB->system_table('users'),
        array('id'),
        array('operator' => 'AND',
            'conditions'=> array('[%]groups' => '%'.$group['alias'].'%')));

// Check the query for errors
if (!$query) {
    $Theamus->Log->query($Theamus->DB->get_last_error()); // Log the query
    
    die($Theamus->Notify('Failed to get affected user count.'));
}

// Define the amount of users in this group
$affected_count = $Theamus->DB->count_rows($query);

// Define the affected statement
$affected = $affected_count == 1 ? '1 user' : $affected_count.' users';

?>

<div class='admin-tabs'><?php echo $Groups->groups_tabs(FILE); ?></div>

<div id='remove-result' style='margin-top: 15px;'></div>

<form class='form-horizontal' id='remove-group-form' style='width: 500px;'>
    <div class='col-12'>
        <input type='hidden' name='id' value='<?php echo $group['id']; ?>' />
        Are you sure you want to remove the group <b><?php echo $group['name']; ?></b>?
        <ul>
            <li>This will affect <?php echo $affected; ?>.</li>
        </ul>
        Removing a group cannot be undone.
    </div>

    <hr class='form-split'>
    
    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Remove</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_groups_tab');
    admin_window_run_on_load('remove_group');
</script>