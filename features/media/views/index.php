<div class='admin-tabs'><?php echo $Media->media_tabs(FILE); ?></div>

<div id='media-result' style='margin-top: 15px;'></div>

<div id='media-list'></div>

<script>
    admin_window_run_on_load('change_media_tab');
    admin_window_run_on_load('get_media_list');
</script>