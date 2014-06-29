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

<div class="admin_content-header">
    <span class="admin_content-header-img">
        <img id="admin_content-header-img" src="features/settings/img/edit-settings.png" alt="" />
    </span>
	<div class="admin_content-header-text">Site Settings</div>
</div>

<div class="admin_page-content">
    <div id="custom-result"></div>
    <form class="admin-form" id="custom-form" onsubmit="return saveSettings();">
        <div class="admin-formrow" row="config-email">
            <div class="admin-formlabel afl-float">
                Configure Email
            </div>
            <div class="admin-forminput">
                <div class="admin-cboxwrapper">
                    <input type="checkbox" class="admin-switchcbox" name="config-email"
                    id="config-email" onchange="showEmailConfig();">
                    <label class="admin-switchlabel yn" for="config-email">
                        <span class="admin-switchinner"></span>
                        <span class="admin-switchswitch"></span>
                    </label>
                </div>
            </div>
            <div class="admin-forminfo">
                <span>Change your email configuration settings.</span>
            </div>
        </div>

        <div id="email-container" class="togrow">
            <div class="admin-formheader">
                Email Configuration
            </div>
            <div class="admin-formrow" row="host">
                <div class="admin-formlabel">
                    Host
                </div>
                <div class="admin-forminput">
                    <input type="text" name="host" id="host" maxlength="100"
                    autocomplete="off" autocapitalize="off" autocorrect="off"
                    spellcheck="off" value="<?=$email['host']?>"/>
                </div>
                <div class="admin-forminfo">
                    <span>The host address for your email service</span>
                </div>
            </div>

            <div class="admin-formrow" row="protocol">
                <div class="admin-formlabel">
                    Protocol
                </div>
                <div class="admin-forminput">
                    <label class="admin-selectlabel" for="protocol">
                        <select name="protocol" id="protocol">
                            <option value="tcp"
                            <?php
                                if ($email['proto'] == "tcp")
                                    echo "selected";
                            ?>> TCP</option>
                            <option value="ssl"
                            <?php
                                if ($email['proto'] == "ssl")
                                    echo "selected";
                            ?>> SSL</option>
                            <option value="tls"
                            <?php
                                if ($email['proto'] == "tls")
                                    echo "selected";
                            ?>> TLS</option>
                        </select> </label>
                </div>
                <div class="admin-forminfo">
                    <span>This is the security you want to use when authenticating/sending
                        emails.</span>
                </div>
            </div>

            <div class="admin-formrow" row="port">
                <div class="admin-formlabel">
                    Port
                </div>
                <div class="admin-forminput">
                    <input type="text" name="port" id="port" maxlength="10"
                    autocomplete="off" autocapitalize="off" autocorrect="off"
                    spellcheck="off" style="width: 75px;" value="<?=$email['port']?>" />
                </div>
                <div class="admin-forminfo">
                    <span>This is the port you want to use to connect to your email
                        service.</span>
                </div>
            </div>

            <hr />

            <div class="admin-formrow" row="email">
                <div class="admin-formlabel">
                    Email Address
                </div>
                <div class="admin-forminput">
                    <input type="email" name="email" id="email" maxlength="150"
                    autocomplete="off" autocapitalize="off" autocorrect="off"
                    spellcheck="off" value="<?=$email['user']?>" />
                </div>
                <div class="admin-forminfo">
                    <span>The email address that will send the emails out.  AKA the username
                        to the email service.</span>
                </div>
            </div>

            <div class="admin-formrow" row="password">
                <div class="admin-formlabel">
                    Password
                </div>
                <div class="admin-forminput">
                    <input type="password" name="password" id="password" maxlength="50"
                    value="<?=$email['password']?>" />
                </div>
                <div class="admin-forminfo">
                    <span>The password to login with the email address provided.</span>
                </div>
            </div>
        </div>

        <div class="admin-formheader">
            Developer Options
        </div>
        <div class="admin-formrow" row="errors">
            <div class="admin-formlabel afl-float">
                Display Errors
            </div>
            <div class="admin-forminput">
                <div class="admin-cboxwrapper">
                    <input type="checkbox" class="admin-switchcbox" name="errors"
                    id="errors" <?=$displayErrors ?>>
                    <label class="admin-switchlabel yn" for="errors">
                        <span class="admin-switchinner"></span>
                        <span class="admin-switchswitch"></span>
                    </label>
                </div>
            </div>
            <div class="admin-forminfo">
                <span>If you choose to display errors, it will show all of the errors
                    that occur on any page.</span>
            </div>
        </div>

        <div class='admin-formheader'>
            Update Theamus
        </div>
        <div class='admin-formrow'>
            <div class='admin-formlabel'>
                Update
            </div>
            <div class='admin-forminput'>
                <input type='button' id='update' value='Check for Updates' />
                <span id='update-result'></span>
            </div>
        </div>
        <div class='admin-formrow'>
            <div class='admin-formlabel'>
                Current Version
            </div>
            <div class='admin-forminput'>
                <span><?=$result['version']?></span>
            </div>
        </div>

        <hr />

        <div class="admin-formsubmitrow">
            <input type="submit" class="admin-greenbtn" value="Save Information" />
        </div>
    </form>
</div>