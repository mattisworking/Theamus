<div class='admin-tabs'><?php echo $Appearance->appearance_tabs(FILE); ?></div>

<?php
try { $info = $Appearance->get_theme_info(filter_input(INPUT_GET, 'id')); }
catch (Exception $ex) { die($Theamus->notify('danger', $ex->getMessage())); }
?>

<div id='remove-result' style='margin-top: 15px;'></div>

<form class='form-horizontal' id='appearance_remove-form' style='width: 500px;'>
    <div class='col-12'>
        <input type='hidden' name='id' id='id' value='<?php echo $info['id']; ?>' />
        Are you sure you want to remove the theme <b><?php echo $info['name']; ?></b>?
        <br/><br/>Removing a theme cannot be undone.
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Remove</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_themes_tab');
    admin_window_run_on_load('remove');
</script>