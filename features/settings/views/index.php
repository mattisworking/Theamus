<?php $home = $Settings->decode_home(); ?>

<div class='admin-tabs'><?php echo $Settings->settings_tabs(FILE); ?></div>

<div id='customize-result' style='margin-top: 15px;'></div>

<div id='values-wrapper'>
    <input type='hidden' id='setting-session' value='<?php if ($home['type'] == 'session') echo 'true'; ?>' />
    <input type='hidden' id='out' value='<?=$Settings->get_session_value($home, 'before')?>' />
    <input type='hidden' id='in' value='<?=$Settings->get_session_value($home, 'after')?>' />
    <input type='hidden' id='type' value='<?=$home['type']?>' />
</div>

<form class='form' id='customize-settings-form' style='width: 700px;'>
    <h2 class='form-header'>Site Name</h2>
    <div class='form-group'>
        <input type='text' class='form-control' name='name' id='name' value='<?php echo $Settings->get_site_name(); ?>' autocomplete='off'>
    </div>

    <h2 class='form-header'>Home Page</h2>
    <div class='col-12'>
        <div id='session-notify' style='display: none;'>
            <div class='alert alert-warning'>
                  You are now setting user's logged <span id='setsesstype'></span> page.
                <div style='float:right;margin-top:-8px;'>
                    <button type='button' class='btn btn-danger' id='cancel-sessions'>Cancel</button>
                    <button type='button' class='btn btn-success' id='sessSave'>Save</button>
                </div>
            </div>
        </div>

        <div id='sessionsAreSet' style='display: <?php echo $home['type'] == 'session' ? 'block;' : 'none;'; ?>'>
            <div class='alert alert-info'>
                You have set your home page to respond via sessions.
                <div style='float:right;margin-top:-8px;'>
                    <button type='button' class='btn btn-danger' name='reset-sessions'>Clear</button>
                </div>
            </div>
        </div>

        <div class='col-3'>
            <ul style='list-style: none; padding: 0;'>
                <li><a href='#' name='type' data-for='page'>Page</a></li>
                <li><a href='#' name='type' data-for='feature'>Feature</a></li>
                <li><a href='#' name='type' data-for='custom'>Custom URL</a></li>
                <li><a href='#' name='type' data-for='require-login'>Require Login</a></li>
                <li><a href='#' name='type' data-for='session'>Session Views</a></li>
            </ul>
        </div>

        <div class='col-9' style='margin-top: 0;'>
            <div id='login-notify' style='display: <?php echo $home['type'] == 'require-login' ? 'block;' : 'none;'; ?>; margin-bottom: 30px;'>
                <input type='hidden' id='required-login' value='' />
                <div class='alert alert-info'>A user will be prompted to login when they first visit this site.
                    <a href='#' name='type' data-for='require-login'>Don't want this?</a>
                </div>
            </div>

            <div id='page-wrapper' style='display: <?php echo $home['type'] == 'page' ? 'block;' : 'none;';?>'>
                <div class='form-group'>
                    <label class='control-label' for='page-id'>Home Page</label>
                    <select class='form-control' name='page-id'>
                        <?php echo $Settings->get_pages_select($home); ?>
                    </select>
                    <p class='form-control-feedback'>
                        Choosing this option will direct your users to a static page that you've created with the Pages feature within the Theamus system.<br><br>
                        If you're looking to have a separate view for users that are logged in and logged out, check out the Session Views tab.
                    </p>
                </div>
            </div>

            <div id='feature-wrapper' style='display: <?php echo $home['type'] == 'feature' ? 'block;' : 'none;'; ?>'>
                <div class='form-group'>
                    <label class='control-label' for='feature'>Feature</label>
                    <select class='form-control' name='feature-id' id='features-select'>
                        <?php echo $Settings->get_features_select($home); ?>
                    </select>
                    <br><br>
                    <label class='control-label' for='feature-files'>Feature File</label>
                    <select class='form-control' name='feature-file' id='feature-files-select'></select>
                    <hr class='form-split'>
                    <p class='form-control-feedback'>
                        If you really want to go to a feature, you just have to select it from the top selection box. That will take you to the index page by default. If you want or need to go to a specific page in the feature, just select a different selection.
                    </p>
                </div>
            </div>

            <div id='no-custom' style='display:none; '>
                <p class='form-control-feedback'>
                    You can't require a login to a custom url, that's just silly.
                    If you want to go to a custom url, you need to turn off the
                    required login. To do that,
                    <a href='#' name='type' data-for='require-login'>click here</a>.
                </p>
            </div>

            <div id='custom-wrapper' style='display: <?php echo $home['type'] == 'custom' ? 'block;' : 'none;'; ?>'>
                <div class='form-group'>
                    <label class='control-label' for='custom-url'>Custom URL</label>
                    <input type='text' class='form-control' name='custom-url' id='custom-url' value='<?php echo array_key_exists('url', $home) ? $home['url'] : '' ?>' autocomplete='off'>
                    <hr class='form-split'>
                    <p class='form-control-feedback'>
                        The Custom URL that you're inputting here is to a specific page within your site. It <b>cannot</b> go to an external site.<br><br>
                        For example, you have a blog and you want to link to a specific post. Your URL would look like: http://www.theamus.com/blog/posts/this-is-a-post<br><br>
                        All you need to input is: blog/posts/this-is-a-post<br><br>
                        Everything else, like the base of the path, is assumed.
                    </p>
                </div>
            </div>

            <div id='nologin' style='display:none; '>
                <div class='afi-col-nopad'>
                    You're setting your session home pages.  If you want to require a login, then you need to cancel the current process and continue from there.
                </div>
            </div>

            <div id='require-login-wrapper' style='display: <?php echo $home['type'] == 'require-login' ? 'block;' : 'none;'; ?>'>
                <div class='form-group'>
                    <label class='checkbox'>
                        <input type='checkbox' name='login' id='reqlogin' <?php echo $home['type'] == 'require-login' ? 'checked' : ''; ?>>
                        Require Login?
                    </label>
                    <hr class='form-split'>
                    <p class='form-control-feedback'>
                        Requiring a login will prompt a user for their credentials. This is only for the home page, though. User's will still be able to view things that have no permissions set on them or are specifically made to be viewable by anyone.<br><br>
                        <b>*NOTE:</b> one you select this, just go choose the view what you want to show up. What you're looking at is what will be saved. If you're not looking at anything, it will default to the default home page.
                    </p>
                </div>
            </div>

            <div id='unsetsession' style='display: <?php echo $home['type'] == 'session' ? 'block;' : 'none;'; ?>'>
                <p class='form-control-feedback'>
                    You've already set your home page up to work with sessions, so you can't do this. If you want to be able to do this,
                    <a href='#' name='reset-sessions'>click here</a>.
                </p>
            </div>

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
    admin_window_run_on_load('customize');
    admin_window_run_on_load('load_feature_files_select');
</script>