<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<div id='pages-list'></div>

<script>
    admin_window_run_on_load('change_pages_tab');
    admin_window_run_on_load('get_pages');
</script>