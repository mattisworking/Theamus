<?php

$get = filter_input_array(INPUT_GET);

if (isset($get['id'])) {
    $id = $get['id'];
    if (is_numeric($id)) {
        $query_group = $tData->select_from_table($tData->prefix."groups", array("id", "alias", "name"), array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        if ($query_group != false) {
            if ($tData->count_rows($query_group) > 0) {
                $group = $tData->fetch_rows($query_group);

                $query_users = $tData->select_from_table($tData->prefix."users", array("id"), array(
                    "operator"  => "",
                    "conditions"=> array(
                        "[%]groups" => "%".$group['alias']."%"
                    )
                ));

                if ($query_users != false) {
                    $affected_count = $tData->count_rows($query_users);
                    $affected = $affected_count == 1 ? "1 user" : $affected_count." users";
                } else {
                    $error[] = "There was an issue querying the users database.";
                }
            } else {
                $error[] = "There was an error when finding the group requested.";
            }
        } else {
            $error[] = "There was an issue querying the database.";
        }
    } else {
        $error[] = "The ID provided isn't valid.";
    }
} else {
    $error[] = "There's no group ID defined.";
}

?>
<div class="window-header">
    <?php echo empty($error) ? "Are you sure?" : "Hmmm..."; ?>
</div>
<div class="window-content">
    <?php
    if (!empty($error)):
        notify("admin", "failure", $error[0]);
    ?>
    <div class="window-options">
        <input type="button" class="admin-purpbtn" onclick="close_remove_group();" value="Close" />
    </div>
    <?php else: ?>
    <input type="hidden" name="group_id" id="group_id" value="<?=$group['id']?>" />
    Are you sure you want to remove the group <b><?=$group['name']?></b>?
    <ul>
        <li>This will affect <?=$affected?>.</li>
    </ul>
    Removing a group cannot be undone.
    <div class="window-options">
        <input type="button" value="OK" onclick="return submit_remove_group();" class="admin-greenbtn" />
        <input type="button" class="admin-redbtn" onclick="close_remove_group();" value="Cancel" />
    </div>
    <?php endif; ?>
</div>