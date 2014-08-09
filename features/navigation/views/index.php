<div class='admin-tabs'><?php echo $Navigation->navigation_tabs(FILE); ?></div>

<div id='navigation-list'></div>

<script>
    admin_window_run_on_load('change_navigation_tab');
    admin_window_run_on_load('get_navigation_links');
</script>