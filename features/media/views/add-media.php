<div class='admin-tabs'><?php echo $Media->media_tabs(FILE); ?></div>

<div id='test'></div>

<div class='add-media'>
    <div id='media_add-list'></div>

    <div id='dnd_area' class='media_dnd-area'>Drag Some Files Here</div>

    <div class='media_dnd-alt'>
        <input type='file' id='tempfile_input' />
        <a href='#' id='dnd_alt-add'>or select files to upload...</a>
    </div>
</div>

<hr class='form-split'>

<div class='form-button-group' style='text-align: right;'>
    <button type='button' class='btn btn-success' id='upload_media'>Upload</button>
</div>

<script>
    admin_window_run_on_load('change_media_tab');
    admin_window_run_on_load('dnd_listen');
</script>