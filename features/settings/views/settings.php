<?php

$query = $tData->select_from_table($tData->prefix."settings");

// Grab settings information
$result = $tData->fetch_rows($query);

// Define page variables
$email['host'] = $result['email_host'];
$email['proto'] = $result['email_protocol'];
$email['port'] = $result['email_port'];
$email['user'] = $result['email_user'];
$email['password'] = $result['email_password'];
$displayErrors = $result['display_errors'] == 1 ? "checked" : "";
?>

<!-- Settings Tabs -->
<div class='admin-tabs'><?php echo $Settings->settings_tabs(FILE); ?></div>

<!-- Form results -->
<div id="custom-result" style='margin-top: 15px;'></div>

<form class="form" id="custom-form" onsubmit="return saveSettings();" style='width: 500px; padding-top: 15px;'>
    <h2 class='form-header'>Site Email Setup</h2>
    <div class="form-group">
        <label class='checkbox'>
            <input type='checkbox' name='config-email' id='config-email' onchange="showEmailConfig();">
            Configure Email Settings
        </label>
    </div>

    <div id="email-container" class='col-12' style='display: none;'>
        <h3 class='form-header'>Email Configuration</h3>
        <div class='form-group'>
            <label class='control-label' for='host'>Host</label>
            <input type='text' class='form-control' name='host' id='host' autocomplete='off' value='<?php echo $email['host']; ?>'>
        </div>

        <div class='form-group'>
            <label class='control-label' for='protocol'>Protocol</label>
            <select class='form-control' name="protocol" id="protocol">
                <option value="tcp" <?php if ($email['proto'] == "tcp") echo "selected";?>> TCP</option>
                <option value="ssl"
                <?php if ($email['proto'] == "ssl") echo "selected"; ?>> SSL</option>
                <option value="tls" <?php if ($email['proto'] == "tls") echo "selected"; ?>> TLS</option>
            </select>
        </div>

        <div class='form-group'>
            <label class='control-label' for='port'>Port</label>
            <input type='text' class='form-control' name='port' id='port' autocomplete='off' value='<?php echo $email['port']; ?>'>
        </div>

        <hr class='form-split'>

        <div class='form-group'>
            <label class='control-label' for='email'>Email Address</label>
            <input type='text' class='form-control' name='email' id='email' autocomplete='off' value='<?php echo $email['user']; ?>'>
        </div>

        <div class='form-group'>
            <label class='control-label' for='password'>Password</label>
            <input type='password' class='form-control' name='password' id='password' value='<?php echo $email['password']; ?>'>
        </div>
    </div>

    <h2 class='form-header'>Developer Options</h2>
    <div class='form-group'>
        <label class='checkbox'>
            <input type='checkbox' name='errors' id='errors' <?php echo $displayErrors; ?>>
            Display errors?
        </label>
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
            <?php echo $result['version']; ?>
        </div>
    </div>

    <hr class='form-split'>

    <div class="form-button-group">
        <button type='submit' class='btn btn-success'>Save Information</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_settings_tab');
</script>