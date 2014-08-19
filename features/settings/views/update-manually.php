<div class='admin-tabs'><?php echo $Settings->settings_tabs(FILE); ?></div>

<div id="settings_update-result" style='margin-top: 15px;'></div>

<form class="form-horizontal" id="settings_update-form" style='width: 500px;'>
    <div class='form-group'>
        <label class='control-label col-3' for='file'>System Files</label>
        <div class='col-9'>
            <input type='file' class='form-control' name='file' id='file'>
        </div>
        <p class='form-control-feedback'>
            All of the files should be in the root of a compressed zip file.<br>
            e.g. theamus-update.zip/features - <b>not</b> theamus-update.zip/theamus/features
        </p>
    </div>

    <div id="settings_prelim-info-wrapper" style="display: none; margin-top: 50px;">
        <h2 class='form-header'>Preliminary Update Information</h2>
        <div id="prelim-notes"></div>
    </div>

    <hr class='form-split'>

    <div class="form-button-group">
        <button type='submit' class='btn btn-success'>Update</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_settings_tab');
    admin_window_run_on_load('settings_manual_update');
</script>