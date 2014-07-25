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
<!-- Features Tabs -->
<div class='admin-tabs'><?php echo $Features->features_tabs(FILE); ?></div>

<!-- Form Results -->
<div id='remove-result' style='margin-top: 15px;'></div>

<?php
if (!empty($error)):
    alert_notify('danger', $error[0]);
else:
?>
<form class='form-horizontal' id='remove-group-form' style='width: 500px;'>
    <div class='col-12'>
        <input type="hidden" name="feature_id" id="feature_id" value="<?=$feature['id']?>" />
        Are you sure you want to remove the feature <b><?=$feature['name']?></b>?
        <br/><br/>Removing a feature cannot be undone.<br /><br />
        <span style="color:#888; font-size:9pt;">
            This will remove any information ever associated with this feature.
            If you want to keep that information, you should back up your database now.
        </span>
    </div>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success' onclick="return submit_remove_feature();">Remove</button>
    </div>
</form>
<?php endif; ?>

<script>
    admin_window_run_on_load('change_features_tab');
</script>