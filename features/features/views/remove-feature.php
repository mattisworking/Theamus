<?php

$get = filter_input_array(INPUT_GET);

if (isset($get['id'])) {
    $id = $get['id'];
    if (is_numeric($id)) {
        $query = $tData->select_from_table($tData->prefix."features", array("id", "name"), array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        if ($query != false) {
            if ($tData->count_rows($query) > 0) {
                $feature = $tData->fetch_rows($query);
            } else {
                $error[] = "There was an error when finding the feature requested.";
            }
        } else {
            $error[] = "There was an issue querying the database.";
        }
    } else {
        $error[] = "The ID provided isn't valid.";
    }
} else {
    $error[] = "There's no feature ID defined.";
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
        <input type="button" class="admin-purpbtn" onclick="close_remove_feature();" value="Close" />
    </div>
    <?php else: ?>
    <input type="hidden" name="feature_id" id="feature_id" value="<?=$feature['id']?>" />
    Are you sure you want to remove the feature <b><?=$feature['name']?></b>?
    <br/><br/>Removing a feature cannot be undone.<br /><br />
    <span style="color:#888; font-size:9pt;">
        This will remove any information ever associated with this feature.
        If you want to keep that information, you should back up your database now.
    </span>
    <div class="window-options">
        <input type="button" value="OK" onclick="return submit_remove_feature();" class="admin-greenbtn" />
        <input type="button" class="admin-redbtn" onclick="close_remove_feature();" value="Cancel" />
    </div>
    <?php endif; ?>
</div>