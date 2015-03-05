<div class="admin-tabs"><?php echo $Settings->settings_tabs(FILE); ?></div>

<div id="settings_logs-listing"></div>

<script>
    admin_window_run_on_load("change_settings_tab");
    admin_window_run_on_load("Admin.Settings.Logs.Pages.view");
</script>