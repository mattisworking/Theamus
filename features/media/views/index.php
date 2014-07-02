<!-- Media Tabs -->
<div class='admin-tabs'><?php echo $Media->media_tabs(FILE); ?></div>

<!-- Remove Media Result -->
<div id='media-result' style='margin-top: 15px;'></div>

<!-- Media List -->
<div id='media-list'></div>

<script>
    admin_window_run_on_load('change_media_tab');
    admin_window_run_on_load('load_media_list');
</script>