<div class='admin-tabs'><?php echo $Features->features_tabs(FILE); ?></div>

<div id='feature-list'></div>

<script>
    admin_window_run_on_load('change_features_tab');
    admin_window_run_on_load('get_features');
</script>