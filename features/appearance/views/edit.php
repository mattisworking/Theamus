<!-- Appearance Tabs -->
<div class='admin-tabs'><?php echo $Appearance->appearance_tabs(FILE); ?></div>

<!-- Edit Results -->
<div id="appearance_edit-result"></div>

<!-- Edit Form -->
<form class="form" id="appearance_edit-form">
    <?php
    try { $info = $Appearance->get_theme_info(); }
    catch (Exception $ex) { $Appearance->print_exception($ex); }
    ?>
    <h2 class='form-header'>Manual Update</h2>
    <div class='form-group'>
        <input type='file' class='form-control' name='file'>
        <div id='appearance_update-result'></div>
    </div>

    <h2 class='form-header'>Theme Settings</h2>
    <div id="theme_settings"></div>

    <hr class='form-split'>

    <div class="form-button-group">
        <button type='submit' class='btn btn-success'>Save</button>
    </div>

    <input type="hidden" name="id" value="<?=$info['id']?>" />
</form>

<script>
    admin_window_run_on_load('change_themes_tab');
    change_admin_window_title('theamus-appearance', 'Edit Theme "<?php echo $info['name']; ?>"');
</script>