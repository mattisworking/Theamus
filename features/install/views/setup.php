<form class='form' id='install_setup-form'>
    <div class='content col-8'>
        <div id='install_setup-result'></div>

        <div class='form-group'>
            <h2 class='form-header'>Site Name</h2>
            <input type='text' class='form-control' name='site_name' autocomplete='off' placeholder='My Website!'>
        </div>

        <hr class='form-split'>

        <div class='col-6'>
            <h2 class='form-header'>Database Configuration</h2>
            <div class='form-group'>
                <label class='control-label' for='database-host'>Database Host</label>
                <input type='text' class='form-control' id='database-host' name='database_host' autocomplete='off' value='localhost'>
            </div>
            <div class='form-group'>
                <label class='control-label' for='database-username'>Database Username</label>
                <input type='text' class='form-control' id='database-username' name='database_username' autocomplete='off'>
            </div>
            <div class='form-group'>
                <label class='control-label' for='database-password'>Database Password</label>
                <input type='password' class='form-control' id='database-password' name='database_password' autocomplete='off'>
            </div>
            <div class='form-group'>
                <label class='control-label' for='database-name'>Database Name</label>
                <input type='text' class='form-control' id='database-name' name='database_name' autocomplete='off'>
            </div>
        </div>

        <div class='col-6 right'>
            <h2 class='form-header'>First User</h2>
            <div class='form-group'>
                <label class='control-label' for='user-username'>Username</label>
                <input type='text' class='form-control' id='username' name='user_username' autocomplete='off'>
            </div>
            <div class='form-group'>
                <label class='control-label' for='password'>Password</label>
                <input type='password' class='form-control' id='user-password' name='user_password' autocomplete='off'>
            </div>
            <div class='form-group'>
                <label class='control-label' for='user-email'>Email Address</label>
                <input type='text' class='form-control' id='user-email' name='user_email' autocomplete='off'>
            </div>
            <div class='form-group col-6'>
                <label class='control-label' for='user-firstname'>First Name</label>
                <input type='text' class='form-control' id='user-firstname' name='user_firstname' autocomplete='off'>
            </div>
            <div class='form-group col-6'>
                <label class='control-label' for='user-lastname'>Last Name</label>
                <input type='text' class='form-control' id='user-lastname' name='user_lastname' autocomplete='off'>
            </div>
        </div>

        <hr class='form-split'>

        <div class='form-group'>
            <label class='checkbox'>
                <input type='checkbox' id='show-advanced-options'>
                Advanced Options?
            </label>
        </div>

        <div id='advanced-options'>
            <div class='col-6'>
                <h2 class='form-header'>Database Customization</h2>
                <div class='form-group'>
                    <label class='control-label' for='database-prefix'>Table Prefix</label>
                    <input type='text' class='form-control' id='database-prefix' name='database_prefix' value='tm_'>
                </div>

                <h2 class='form-header' style='margin-top: 50px;'>Email Configuration</h2>
                <div class='form-group'>
                    <label class='control-label' for='email-host'>Host</label>
                    <input type='text' class='form-control' id='email-host' name='email-host' placeholder='smtp.example.com' autocomplete='off'>
                </div>
                <div class='form-group'>
                    <label class='control-label' for='email-protocol'>Protocol</label>
                    <select class='form-control' id='email-protocol' name='email-protocol'>
                        <option value='tcp'>TCP</option>
                        <option value='ssl'>SSL</option>
                        <option value='tls'>TLS</option>
                    </select>
                </div>
                <div class='form-group'>
                    <label class='control-label' for='email-port'>Port</label>
                    <input type='text' class='form-control form-control-inline' id='email-port' name='email-port' placeholder='25' autocomplete='off'>
                </div>
                <div class='form-group'>
                    <label class='control-label' for='email-login-username'>Login Username</label>
                    <input type='text' class='form-control' id='email-login-username' name='email-login-username' placeholder='username | user@example.com' autocomplete='off'>
                </div>
                <div class='form-group'>
                    <label class='control-label' for='email-login-password'>Login Password</label>
                    <input type='password' class='form-control' id='email-login-password' name='email-login-password'>
                </div>
            </div>

            <div class='col-6'>
                <h2 class='form-header'>Security</h2>
                <div class='form-group'>
                    <label class='control-label' for='security-password-salt'>Password Salt</label>
                    <div class='input-group'>
                        <input type='text' class='form-control' id='security-password-salt' name='security_password-salt' placeholder='' autocomplete='off'>
                        <span class='input-group-btn'>
                            <button type='button' class='btn btn-default' id='generate-password-salt'>Generate</button>
                        </span>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='control-label' for='security-session-salt'>Session Salt</label>
                    <div class='input-group'>
                        <input type='text' class='form-control' id='security-session-salt' name='security_session-salt' placeholder='' autocomplete='off'>
                        <span class='input-group-btn'>
                            <button type='button' class='btn btn-default' id='generate-session-salt'>Generate</button>
                        </span>
                    </div>
                </div>

                <h2 class='form-header' style='margin-top: 50px;'>Developer Options</h2>
                <div class='form-group'>
                    <label class="checkbox">
                        <input type="checkbox" name="developer-mode">
                        Turn on Developer Mode
                    </label>
                    <p class="help-block">Turning on Developer Mode allows access to things that wouldn't be a great idea to have on a production site.  These things include showing errors, seeing page information, and more.</p>
                </div>
            </div>
        </div>

        <div class='clearfix'></div>

        <hr class='form-split'>

        <div class='form-button-group'>
            <button type='submit' class='btn btn-primary'>Install Theamus</button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        install_setup();
    });
</script>