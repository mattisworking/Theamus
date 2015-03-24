<div class="admin-tabs"><?php echo $Features->features_tabs(FILE); ?></div>

<div id="features_create-result"
    style="margin-top: 15px;"></div>

<form class="form-horizontal features_create-form"
    id="features_create-form">
    
    <div class="form-group">
        <label class="control-label col-2"
            for="features_create-feature-name">
                Feature Name
        </label>
        
        <div class="col-10">
            <input type="text"
                class="form-control"
                id="features_create-feature-name"
                name="features_create-feature-name"
                placeholder="The Greatest Calculator Ever">

            <p class="help-block">
                This is the pretty name of the feature.  It's what will show up when an administrator is looking to install/manage his/her features.
            </p>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-2"
            for="features_create-feature-alias">
                Feature Alias
        </label>
        
        <div class="col-10">
            <input type="text"
                class="form-control"
                id="features_create-feature-alias"
                name="features_create-feature-alias"
                placeholder="great_calc">
            
            <p class="help-block">
                The feature alias will be the name of the folder that the feature lives in.  Make it unique!  It's not relevant to anything that can be seen on/through the web/browser.  This is strictly a programming/developer value.
            </p>
        </div>
    </div>

    <hr class="form-split">
    
    <div class="form-button-group">
        <button type="submit"
            class="btn btn-success">
                    Create Feature
        </button>
    </div>
    
</form>

<script>
    admin_window_run_on_load("change_features_tab");
    admin_window_run_on_load("Admin.Features.Developer.createFeatureListener");
</script>