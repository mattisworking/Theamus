<?php $h = $Settings->get_home_info(); ?>

<!-- Settings Tabs -->
<div class='admin-tabs'><?php echo $Settings->settings_tabs(FILE); ?></div>

<!-- Customization form result -->
<div id='custom-result' style='margin-top: 15px;'></div>

<!-- Hidden values -->
<div id='values-wrapper'>
    <input type='hidden' id='setting-session' value='<?php if ($h['type'] == 'session') echo 'true'; ?>' />
    <input type='hidden' id='out' value='<?=$Settings->get_session_value($h, 'before')?>' />
    <input type='hidden' id='in' value='<?=$Settings->get_session_value($h, 'after')?>' />
    <input type='hidden' id='type' value='<?=$h['type']?>' />
</div>

<!-- Customization form -->
<form class='form' id='custom-form' style='width: 700px;'>
    <!-- Site name -->
    <div class='form-group'>
        <h2 class='form-header'>Site Name</h2>
        <input type='text' class='form-control' name='name' id='name' value='<?php echo $Settings->get_site_name(); ?>' autocomplete='off'>
    </div>

    <!-- Home page -->
    <h2 class='form-header'>Home Page</h2>
    <div class='col-12'>
        <!-- Session home page notification (when setting) -->
        <div id='session-notify' style='display: none;'>
            <div class='alert alert-warning'>
                  You are now setting user's logged <span id='setsesstype'></span> page.
                <div style='float:right;margin-top:-8px;'>
                    <button type='button' class='btn btn-danger' id='cancel-sessions'>Cancel</button>
                    <button type='button' class='btn btn-success' id='sessSave'>Save</button>
                </div>
            </div>
        </div>

        <!-- Session home page notification (when set) -->
        <?php $display = $h['type'] == 'session' ? 'block' : 'none'; ?>
        <div id='sessionsAreSet' style='display: <?php echo $display; ?>'>
            <div class='alert alert-info'>
                You have set your home page to respond via sessions.
                <div style='float:right;margin-top:-8px;'>
                    <button type='button' class='btn btn-danger' name='reset-sessions'>Clear</button>
                </div>
            </div>
        </div>

        <!-- Home page selections -->
        <div class='col-3'>
            <ul style='margin: 0; padding: 0;'>
                <li><a href='#' name='type' data-for='page'>Page</a></li>
                <li><a href='#' name='type' data-for='feature'>Feature</a></li>
                <li><a href='#' name='type' data-for='custom'>Custom URL</a></li>
                <li><a href='#' name='type' data-for='require-login'>Require Login</a></li>
                <li><a href='#' name='type' data-for='session'>Session Views</a></li>
            </ul>
        </div>

        <div class='col-9' style='margin-top: 0;'>
            <!-- Login notification -->
            <?php $display = $h['type'] == 'require-login' ? 'block' : 'none'; ?>
            <div id='login-notify' style='display: <?php echo $display; ?>; margin-bottom: 30px;'>
                <input type='hidden' id='required-login' value='' />
                <div class='alert alert-info'>A user will be prompted to login when they first visit this site.
                    <a href='#' name='type' data-for='require-login'>Don't want this?</a>
                </div>
            </div>

            <!-- Pages -->
            <?php $display = $h['type'] == 'page' ? 'display:block;' : 'display:none;'; ?>
            <div id='page-wrapper' style='<?=$display?>'>
                <div class='form-group'>
                    <label class='control-label' for='page-id'>Home Page</label>
                    <?php echo $Settings->get_pages_select($h); ?>
                    <p class='form-control-feedback'>
                        Choosing this option will direct your users to a static page that you've created with the Pages feature within the Theamus system.<br><br>
                        If you're looking to have a separate view for users that are logged in and logged out, check out the Session Views tab.
                    </p>
                </div>
            </div>

            <!-- Features -->
            <?php $display = $h['type'] == 'feature' ? 'display:block;' : 'display:none;'; ?>
            <div id='feature-wrapper' style='<?=$display?>'>
                <div class='form-group'>
                    <label class='control-label'>Feature</label>
                    <?php echo $Settings->get_features_select($h); ?>
                    <br><br>
                    <label class='control-label'>Feature File</label>
                    <div id='feature-file-list'></div>
                    <hr class='form-split'>
                    <p class='form-control-feedback'>
                        If you really want to go to a feature, you just have to select it from the top selection box. That will take you to the index page by default. If you want or need to go to a specific page in the feature, just select a different selection.
                    </p>
                </div>
            </div>

            <!-- Custom URL -->
            <?php
            $display = $h['type'] == 'custom' ? 'display:block;' : 'display:none;';
            $h['url'] = array_key_exists('url', $h) ? $h['url'] : '';
            ?>
            <!-- Session for custom URL error -->
            <div id='no-custom' style='display:none; '>
                <p class='form-control-feedback'>
                    You can't require a login to a custom url, that's just silly.
                    If you want to go to a custom url, you need to turn off the
                    required login. To do that,
                    <a href='#' name='type' data-for='require-login'>click here</a>.
                </p>
            </div>

            <div id='custom-wrapper' style='<?=$display?>'>
                <div class='form-group'>
                    <label class='control-label' for='custom-url'>Custom URL</label>
                    <input type='text' class='form-control' name='custom-url' id='custom-url' value='<?php echo $h['url']; ?>' autocomplete='off'>
                    <hr class='form-split'>
                    <p class='form-control-feedback'>
                        The Custom URL that you're inputting here is to a specific page within your site. It <b>cannot</b> go to an external site.<br><br>
                        For example, you have a blog and you want to link to a specific post. Your URL would look like: http://www.theamus.com/blog/posts/this-is-a-post<br><br>
                        All you need to input is: blog/posts/this-is-a-post<br><br>
                        Everything else, like the base of the path, is assumed.
                    </p>
                </div>
            </div>

            <!-- Require Login -->
            <?php
            $display = $h['type'] == 'require-login' ? 'display:block;' : 'display:none;';
            $check = $h['type'] == 'require-login' ? 'checked' : '';
            ?>
            <!-- Already setting sessions notification -->
            <div id='nologin' style='display:none; '>
                <div class='afi-col-nopad'>
                    You're setting your session home pages.  If you want to require a login, then you need to cancel the current process and continue from there.
                </div>
            </div>

            <div id='require-login-wrapper' style='<?=$display?>'>
                <div class='form-group'>
                    <label class='checkbox'>
                        <input type='checkbox' name='login' id='reqlogin' <?php echo $check; ?>>
                        Require Login?
                    </label>
                    <hr class='form-split'>
                    <p class='form-control-feedback'>
                        Requiring a login will prompt a user for their credentials. This is only for the home page, though. User's will still be able to view things that have no permissions set on them or are specifically made to be viewable by anyone.<br><br>
                        <b>*NOTE:</b> one you select this, just go choose the view what you want to show up. What you're looking at is what will be saved. If you're not looking at anything, it will default to the default home page.
                    </p>
                </div>
            </div>

            <!-- Session Control -->
            <?php $display = $h['type'] == 'session' ? 'display:block;' : 'display:none;'; ?>
            <!-- Unset sessions notification -->
            <div id='unsetsession' style='<?=$display?>'>
                <p class='form-control-feedback'>
                    You've already set your home page up to work with sessions, so you can't do this. If you want to be able to do this,
                    <a href='#' name='reset-sessions'>click here</a>.
                </p>
            </div>

            <!-- Requiring login notification -->
            <div id='no-session' style='display:none;'>
                <p class='form-control-feedback'>
                    You are already requiring a login, all you have to do now is choose a page or feature that users will go to once they've logged in!
                    <a href='#' name='type' data-for='require-login'>Turn of the required login here</a>.
                </p>
            </div>

            <div id='session-wrapper' style='display:none;'>
                <div class='form-group'>
                    <button type='button' class='btn btn-default' id='set-sessions'>Set them Now</button>
                    <hr class='form-split'>
                    <p class='form-control-feedback'>
                        Setting the session views works exactly how you think it would. When you go to click on 'Set them Now' the website will capture the pages that you set. The logged in page first, then the logged out page.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Save Information</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_settings_tab');
</script>