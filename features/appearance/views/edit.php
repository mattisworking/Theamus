<div class='admin-tabs'><?php echo $Appearance->appearance_tabs(FILE); ?></div>

<?php
try { $info = $Appearance->get_theme_info(filter_input(INPUT_GET, 'id')); }
catch (Exception $ex) { die($Theamus->notify('danger', $ex->getMessage())); }
?>

<div id='appearance_edit-result' style='margin-top: 15px;'></div>

<form class='form' id='appearance_edit-form'>
    <h2 class='form-header'>Manual Update</h2>
    <div class='form-group'>
        <input type='file' class='form-control' name='file'>
        <div id='appearance_update-result'></div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Save</button>
    </div>

    <input type='hidden' name='id' value='<?=$info['id']?>' />
</form>

<script>
    admin_window_run_on_load('change_themes_tab');
    admin_window_run_on_load('edit');
    change_admin_window_title('theamus-appearance', 'Edit Theme "<?php echo $info['name']; ?>"');
</script>