<div class='admin-tabs'><?php echo $Settings->settings_tabs(FILE); ?></div>

<div id='auto-update-result' style='margin-top: 15px;'></div>

<div id='checker-wrapper'></div>

<input type='hidden' id='current_version' value='<?php echo $Theamus->settings['version']; ?>'>

<script>
    admin_window_run_on_load('change_settings_tab');
    admin_window_run_on_load('update_check');
</script>