<div class='admin-tabs'><?php echo $Features->features_tabs(FILE); ?></div>

<div id='feature_install-result' style='margin-top: 15px;'></div>

<form class='form' id='feature_install-form'>
    <div class='form-group'>
        <input type='file' class='form-control' name='file'>
        <p class='form-control-feedback'>
            Features should come in the form of zip archives.<br />
            Select the feature you want to install and everything will be handled automatically from there.
        </p>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success' id='feature_install-button'>Install</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_features_tab');
    admin_window_run_on_load('install_feature');
</script>