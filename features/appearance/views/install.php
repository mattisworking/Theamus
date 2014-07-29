<!-- Appearance Tabs -->
<div class='admin-tabs'><?php echo $Appearance->appearance_tabs(FILE); ?></div>

<!-- Upload Result -->
<div id="upload-result" style='margin-top: 15px;'></div>

<!-- Upload form -->
<form class="form" id="appearance_install-form">
    <h2 class='form-header'>Theme Files</h2>
    <div class='form-group'>
        <input type='file' class='form-control' name='file'>
        <p class='form-control-feedback'>
            Themes should come in the form of zip archives.<br />
            Select the theme you want to install and everything will be handled automatically from there.
        </p>
    </div>

    <div id="appearance_prelim-info-wrapper" style="display: none;">
        <h2 class='form-header'>Preliminary Installation Information</h2>
        <div id="prelim-notes"></div>
    </div>

    <hr class='form-split'>

    <div class="form-button-group">
        <button type='submit' class='btn btn-success'>Install</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_themes_tab');
</script>