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
<!-- Appearance Tabs -->
<div class='admin-tabs'><?php echo $Appearance->appearance_tabs(FILE); ?></div>

<!-- Form Results -->
<div id='remove-result' style='margin-top: 15px;'></div>

<?php
if (!empty($error)):
    alert_notify('danger', $error[0]);
else:
?>
<form class='form-horizontal' id='remove-group-form' style='width: 500px;'>
    <div class='col-12'>
        <input type="hidden" name="theme_id" id="theme_id" value="<?=$theme['id']?>" />
        Are you sure you want to remove the theme <b><?=$theme['name']?></b>?
        <br/><br/>Removing a theme cannot be undone.
    </div>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success' onclick="return submit_remove_theme();">Remove</button>
    </div>
</form>
<?php endif; ?>

<script>
    admin_window_run_on_load('change_themes_tab');
</script>