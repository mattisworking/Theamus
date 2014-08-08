<div class='admin-tabs'><?php echo $Groups->groups_tabs(FILE); ?></div>

<div id='groups-list'></div>

<script>
    admin_window_run_on_load('change_groups_tab');
    admin_window_run_on_load('load_groups_list');
</script>