<?php

$get = filter_input_array(INPUT_GET);

if (isset($get['id'])) {
    $id = $get['id'];
    if (is_numeric($id)) {
        $query = $tData->select_from_table($tData->prefix."themes", array("id", "name"), array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        if ($query != false) {
            if ($tData->count_rows($query) > 0) {
                $theme = $tData->fetch_rows($query);
            } else {
                $error[] = "There was an error when finding the theme requested.";
            }
        } else {
            $error[] = "There was an issue querying the database.";
        }
    } else {
        $error[] = "The ID provided isn't valid.";
    }
} else {
    $error[] = "There's no theme ID defined.";
}

?>
<div class="window-header">
    <?php if (empty($error)): ?>
    Are you sure?
    <?php else: ?>
    Hmmm...
    <?php endif; ?>
</div>
<div class="window-content">
    <?php
    if (!empty($error)):
        notify("admin", "failure", $error[0]);
    ?>
    <div class="window-options">
        <input type="button" class="admin-purpbtn" onclick="close_remove_theme();" value="Close" />
    </div>
    <?php else: ?>
    <input type="hidden" name="theme_id" id="theme_id" value="<?=$theme['id']?>" />
    Are you sure you want to remove the theme <b><?=$theme['name']?></b>?
    <br/><br/>Removing a theme cannot be undone.
    <div class="window-options">
        <input type="button" value="OK" onclick="return submit_remove_theme();" class="admin-greenbtn" />
        <input type="button" class="admin-redbtn" onclick="close_remove_theme();" value="Cancel" />
    </div>
    <?php endif; ?>
</div>