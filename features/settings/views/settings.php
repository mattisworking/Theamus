<div class='admin-tabs'><?php echo $Settings->settings_tabs(FILE); ?></div>

<div id='settings-result' style='margin-top: 15px;'></div>

<form class='form' id='edit-settings-form' style='width: 500px; margin-top: 15px;'>
    <h2 class='form-header'>Site Email Setup</h2>
    
    <div class="form-group">
        <div id="settings_test-email-result"></div>
        <div class="input-group">
            <input type="email" placeholder="send to email..." class="form-control" id="settings_test-email">
            <span class="input-group-btn">
                <button type="button" class="btn btn-default" id="settings_test-email-button">Test Email</button>
            </span>
        </div>
        <span class="help-block">Testing will use the information filled out in the "Configure Email Settings" section.</span>
    </div>
    
    <div class='form-group'>
        <label class='checkbox'>
            <input type='checkbox' name='config-email' id='config-email'>
            Configure Email Settings
        </label>
    </div>

    <div id='email-container' class='col-12' style='display: none;'>
        <h3 class='form-header'>Email Configuration</h3>
        <div class='form-group'>
            <label class='control-label' for='host'>Host</label>
            <input type='text' class='form-control' name='host' id='host' autocomplete='off' value='<?php echo $Theamus->settings['email_host']; ?>'>
        </div>

        <div class='form-group'>
            <label class='control-label' for='protocol'>Protocol</label>
            <select class='form-control' name='protocol' id='protocol'>
                <option value='tcp' <?php if ($Theamus->settings['email_protocol'] == 'tcp') echo 'selected';?>> TCP</option>
                <option value='ssl' <?php if ($Theamus->settings['email_protocol'] == 'ssl') echo 'selected'; ?>> SSL</option>
                <option value='tls' <?php if ($Theamus->settings['email_protocol'] == 'tls') echo 'selected'; ?>> TLS</option>
            </select>
        </div>

        <div class='form-group'>
            <label class='control-label' for='port'>Port</label>
            <input type='text' class='form-control' name='port' id='port' autocomplete='off' value='<?php echo $Theamus->settings['email_port']; ?>'>
        </div>

        <hr class='form-split'>

        <div class='form-group'>
            <label class='control-label' for='email'>Email Address</label>
            <input type='text' class='form-control' name='email' id='email' autocomplete='off' value='<?php echo $Theamus->settings['email_user']; ?>'>
        </div>

        <div class='form-group'>
            <label class='control-label' for='password'>Password</label>
            <input type='password' class='form-control' name='password' id='password' value='<?php echo $Theamus->decrypt_string($Theamus->settings['email_password']); ?>'>
        </div>
    </div>

    <h2 class='form-header'>Developer Options</h2>
    <div class='form-group'>
        <label class='checkbox'>
            <input type='checkbox' name='errors' id='errors' <?php echo $Theamus->settings['display_errors'] == 1 ? 'checked' : ''; ?>>
            Display errors?
        </label>
    </div>
    
    <div class="form-group">
        <label class="control-label"
               for="settings_log-categories">Log Categories</label>
        <select class="form-control"
                id="settings_log-categories"
                name="settings_log-categories"
                multiple="multiple"
                size="3">
            <option value="general" <?php if (strpos($Theamus->settings['logging'], "general") !== false) echo "selected"; ?>>General</option>
            <option value="developer" <?php if (strpos($Theamus->settings['logging'], "developer") !== false) echo "selected"; ?>>Developer</option>
            <option value="query" <?php if (strpos($Theamus->settings['logging'], "query") !== false) echo "selected"; ?>>Query</option>
        </select>
    </div>
    
    <div class="form-group">
        <label class="control-label"
               for="settings_page-information">Show Page Information</label>
        <select class="form-control"
                if="settings_page-information"
                name="page_information"
                multiple="multiple"
                size="2">
            <option value="load_time" <?php if (strpos($Theamus->settings['show_page_information'], "load_time") !== false) echo "selected"; ?>>Page Load Time</option>
            <option value="query_count" <?php if (strpos($Theamus->settings['show_page_information'], "query_count") !== false) echo "selected"; ?>>Query Count</option>
        </select>
    </div>

    <h2 class='form-header'>Update Theamus</h2>
    <div class='form-group'>
        <label class='control-label col-3' style='margin-top: 9px'>Update</label>
        <div class='col-9'>
            <button type='button' class='btn btn-default' id='update'>Check for Updates</button>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-4'>Current Version</label>
        <div class='col-8'>
            <?php echo $Theamus->version; ?>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Save Information</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_settings_tab');
    admin_window_run_on_load("test_email_listener");
    admin_window_run_on_load('settings');
</script>