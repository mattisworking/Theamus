<div class='admin-tabs'><?php echo $Features->features_tabs(FILE); ?></div>

<?php

try { $feature = $Features->get_feature(filter_input(INPUT_GET, 'id')); }
catch (Exception $ex) { die($Theamus->notify('danger', $ex->getMessage())); }

?>

<div id='feature_remove-result' style='margin-top: 15px;'></div>

<form class='form-horizontal' id='feature_remove-form' style='width: 500px;'>
    <div class='col-12'>
        <input type='hidden' name='id' value='<?php echo $feature['id']; ?>' />
        Are you sure you want to remove the feature <b><?php echo $feature['name']; ?></b>?
        <br/><br/>Removing a feature cannot be undone.<br /><br />
        <span style='color:#888; font-size:9pt;'>
            This will remove any information ever associated with this feature.
            If you want to keep that information, you should back up your database now.
        </span>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Remove</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_features_tab');
    admin_window_run_on_load('remove_feature');
</script>