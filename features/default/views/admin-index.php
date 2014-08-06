<div class='admin-tabs'><?php echo $DefaultAdmin->admin_tabs(FILE); ?></div>

<div id='home-result'></div>

<div id='apps' class='dashboard-apps'><?=$this->include_file('admin/apps')?></div>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('change_admin_tab');
        admin_window_run_on_load('enable_sort');
        admin_window_run_on_load('update_apps');
    });
</script>