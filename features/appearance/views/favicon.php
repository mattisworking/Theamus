<div class="admin-tabs"><?php echo $Appearance->appearance_tabs(FILE); ?></div>

<div id="appearance_favicon-result" style="margin-top: 15px;"></div>

<form class="form"
      id="appearance_favicon-form"
      style="width: 500px;">
    
    <div class="form-group">
        <input type="input"
               class="form-control"
               id="apperance_favicon-path"
               name="appearance_favicon-path"
               placeholder="relative/web/path/to/favicon.ico"
               value="<?php echo $Theamus->settings['favicon_path']; ?>">
        
        <p class="help-block">
            Upload your favicon file via "Media" and then get the relative path from
            the media information.  Use that path to define the path to the favicon
        </p>
    </div>
    
    <hr class="form-split">
    
    <div class="form-group">
        <button type="submit"
                class="btn btn-success">Save</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_themes_tab');
    admin_window_run_on_load("update_favicon_listener");
</script>