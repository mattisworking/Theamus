<div class='admin-tabs'><?php echo $Appearance->appearance_tabs(FILE); ?></div>

<div id='themes-result' style='margin-top: 15px'></div>

<div id='themes-list'></div>

<script>
    admin_window_run_on_load('change_themes_tab');
    admin_window_run_on_load('get_themes');
</script>