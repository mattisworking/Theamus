<?php
$tUser = new tUser();
$user = $tUser->user;
?>
<div id="admin_panel">
    <script>add_js_file('themes/admin/js/admin.js');</script>
    <header class="admin_header">
        <div class="pull-left">
            <ul>
                <li>
                    <span class="admin_header-icon admin_header-logo-icon"></span>
                    <a href="http://www.theamus.com/">theamus</a>
                    <ul>
                        <li><a href="#" onclick="return admin_go('', 'settings/about-theamus/');">About Theamus</a></li>
                        <li class="admin_header-hr"></li>
                        <li><a href="http://www.theamus.com/wiki/">Documentation</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" onclick="return open_admin();">Admin Panel</a>
                </li>
                <li>
                    <span class="admin_header-icon admin_header-new-icon"></span>
                    New
                    <ul style="width: 100px;">
                        <li><a href="#" onclick="return admin_go('accounts', 'accounts/add/')">User</a></li>
                        <li><a href="#" onclick="return admin_go('accounts', 'groups/create/')">Group</a></li>
                        <li><a href="#" onclick="return admin_go('features', 'features/install/')">Feature</a></li>
                        <li><a href="#" onclick="return admin_go('pages', 'pages/create/')">Page</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="pull-right">
            <ul class="admin_header-right">
                <li>
                    <?php if ($user['picture'] != ""): ?>
                    <span class="admin_header-noicon">
                        <img src="media/profiles/<?=$user['picture']?>" />
                    </span>
                    <?php else: ?>
                    <span class="admin_header-icon admin_header-user-icon"></span>
                    <?php endif; ?>
                    <a href="#"><?=$user['firstname']?> <?=$user['lastname']?></a>
                    <ul>
                        <li><a href="accounts/user/edit-account">Edit Profile</a></li>
                        <li class="admin_header-hr"></li>
                        <li><a href="#" onclick="return user_logout();">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </header>

    <nav id="admin-nav" class="admin_nav">
        <ul class="admin_nav-list">
            <li>
                <a href="#" onclick="return close_admin();">
                    <span class="admin_nav-icon admin_nav-close-icon"></span>
                    <span class="admin_nav-text">Close Admin</span>
                </a>
            </li>
            <li parent="home">
                <a href="#" onclick="return admin_go('home', 'default/adminHome/')">
                    <span class="admin_nav-icon admin_nav-home-icon"></span>
                    <span class="admin_nav-text">Admin Home</span>
                </a>
            </li>
            <li parent="accounts" class="current">
                <a href="#" onclick="return admin_go('accounts', 'accounts/')">
                    <span class="admin_nav-icon admin_nav-accounts-icon"></span>
                    <span class="admin_nav-text">Accounts</span>
                </a>
                <ul>
                    <li><a href="#" onclick="return admin_go('accounts', 'accounts/')">View All Users</a></li>
                    <li><a href="#" onclick="return admin_go('accounts', 'accounts/add/')">Add a New User</a></li>
                    <li class="admin_nav-hr"></li>
                    <li><a href="#" onclick="return admin_go('accounts', 'groups/')">View All Groups</a></li>
                    <li><a href="#" onclick="return admin_go('accounts', 'groups/create/')">Create a New Group</a></li>
                    <li class="admin_nav-hr"></li>
                    <li><a href="accounts/user/edit-account/">
                            Your Profile</a></li>
                </ul>
            </li>
            <li parent="media">
                <a href="#" onclick="return admin_go('media', 'media/')">
                    <span class="admin_nav-icon admin_nav-media-icon"></span>
                    <span class="admin_nav-text">Media</span>
                </a>
            </li>
            <li parent="pages">
                <a href="#" onclick="return admin_go('pages', 'pages/')">
                    <span class="admin_nav-icon admin_nav-pages-icon"></span>
                    <span class="admin_nav-text">Pages & Navigation</span>
                </a>
                <ul>
                    <li><a href="#" onclick="return admin_go('pages', 'pages/')">View All Pages</a></li>
                    <li><a href="#" onclick="return admin_go('pages', 'pages/create/')">Create a New Page</a></li>
                    <li class="admin_nav-hr"></li>
                    <li><a href="#" onclick="return admin_go('pages', 'navigation/')">View All Links</a></li>
                    <li><a href="#" onclick="return admin_go('pages', 'navigation/create/')">Create a New Link</a></li>
                </ul>
            </li>
            <li parent="features">
                <a href="#" onclick="return admin_go('features', 'features/')">
                    <span class="admin_nav-icon admin_nav-features-icon"></span>
                    <span class="admin_nav-text">Features</span>
                </a>
                <ul>
                    <li><a href="#" onclick="return admin_go('features', 'features/')">View All Features</a></li>
                    <li><a href="#" onclick="return admin_go('features', 'features/install/')">Install a New Feature</a></li>
                    <li class="admin_nav-hr"></li>
                    <li><a href="#" onclick="return admin_go('features', 'features/notready/')">Browse Features</a></li>
                </ul>
            </li>
            <li parent="settings">
                <a href="#" onclick="return admin_go('settings', 'settings/')">
                    <span class="admin_nav-icon admin_nav-settings-icon"></span>
                    <span class="admin_nav-text">Settings</span>
                </a>
                <ul>
                    <li><a href="#" onclick="return admin_go('settings', 'settings/')">Site Customization</a></li>
                    <li><a href="#" onclick="return admin_go('settings', 'settings/settings/')">Site Settings</a></li>
                    <li class="admin_nav-hr"></li>
                    <li><a href="#" onclick="return admin_go('settings', 'appearance/')">Site Themes</a></li>
                </ul>
            </li>
        </ul>
        <div class="admin_nav-footer">
            theamus - 2014
        </div>
    </nav>

    <div id="admin-content" class="admin_content-wrapper">
        <div id="admin_loading-bg" class="admin_loading-bg"></div>
        <div id="admin_loading-gif" class="admin_loading-gif">
            <img src="themes/admin/img/loading.gif" /></div>
        <div class="admin_content">
            <div id="admin_page-content"></div>
            <div class="admin_content-footer">thanks for using theamus =]</div>
        </div>
    </div>
</div>