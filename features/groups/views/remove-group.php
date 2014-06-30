<?php

$get = filter_input_array(INPUT_GET);

if (isset($get['id'])) {
    $id = $get['id'];
    if (is_numeric($id)) {
        $query_group = $tData->select_from_table($tData->prefix.'groups', array('id', 'alias', 'name'), array(
            'operator'  => '',
            'conditions'=> array('id' => $id)
        ));

        if ($query_group != false) {
            if ($tData->count_rows($query_group) > 0) {
                $group = $tData->fetch_rows($query_group);

                $query_users = $tData->select_from_table($tData->prefix.'users', array('id'), array(
                    'operator'  => 'AND',
                    'conditions'=> array(
                        '[%]value'  => '%'.$group['alias'].'%',
                        'key'       => 'groups'
                    )
                ));

                if ($query_users != false) {
                    $affected_count = $tData->count_rows($query_users);
                    $affected = $affected_count == 1 ? '1 user' : $affected_count.' users';
                } else {
                    $error[] = 'There was an issue querying the users database.';
                }
            } else {
                $error[] = 'There was an error when finding the group requested.';
            }
        } else {
            $error[] = 'There was an issue querying the database.';
        }
    } else {
        $error[] = 'The ID provided isn\'t valid.';
    }
} else {
    $error[] = 'There\'s no group ID defined.';
}

?>
<!-- Groups Tabs -->
<div class='admin-tabs'><?php echo $Groups->groups_tabs(FILE); ?></div>

<!-- Form Results -->
<div id='remove-result'></div>

<?php
if (!empty($error)):
    alert_notify('danger', $error[0]);
else:
?>
<form class='form-horizontal' id='remove-group-form' style='width: 500px;'>
    <div class='col-12'>
        <input type='hidden' name='group_id' id='group_id' value='<?=$group['id']?>' />
        Are you sure you want to remove the group <b><?=$group['name']?></b>?
        <ul>
            <li>This will affect <?=$affected?>.</li>
        </ul>
        Removing a group cannot be undone.
    </div>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Remove</button>
    </div>
</form>
<?php endif; ?>

<script>
    admin_window_run_on_load('change_groups_tab');

    $(document).ready(function() {
        $('#remove-group-form').submit(function(e) {
            e.preventDefault();

            theamus.ajax.run({
                url: 'groups/remove/',
                result: 'remove-result',
                extra_fields: 'group_id',
                after: function() {
                    change_admin_window_title('theamus-groups', 'Theamus Groups');
                    update_admin_window_content('theamus-groups', 'groups/index/');
                }
            });
        });
    });
</script>