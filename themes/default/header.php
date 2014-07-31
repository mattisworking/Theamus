<div class="black_bar">
    <div id="result" style="visibility: hidden;"></div>
    <header class="content-wrapper header-wrapper">
        <div class="site_header-company left">
            <a href="#">
                <span class="site_header-company-logo">
                    <!-- Company logo -->
                </span>
                <span class="site_header-company-text">
                    <?php echo $tTheme->get_system_variable("name"); ?>
                </span>
            </a>
        </div>

        <?php if (!$tUser->is_admin()): ?>
        <div class="site_header-user right">
            <?php if ($tUser->user != false): ?>
            <ul class="nav nav-inline">
                <li>
                    <div class="site_header-user-pic">
                        <img src="media/profiles/<?php echo $tUser->user['picture'] != "" ?
                            $tUser->user['picture'] : "default-user-picture.png"; ?>" alt="" />
                    </div>
                    <div class="site_header-user-name">
                        <?php echo $tUser->user['firstname']." ".$tUser->user['lastname']; ?>
                    </div>
                    <div class="site_header-user-arrow"></div>
                    <div class="clearfix"></div>

                    <ul>
                        <li><a href="accounts/user/edit-account/">Edit Profile</a></li>
                        <li class="site_header-hr"></li>
                        <li><a href="#" onclick="return user_logout();">Logout</a></li>
                    </ul>
                </li>
            </ul>
            <?php else: ?>
            <ul  class="nav nav-inline">
                <li><a href="accounts/login/">Login</a></li>
                <li><a href="accounts/register/">Register</a></li>
            </ul>
            <?php endif; // End user not logged in ?>
        </div>
        <?php endif; // end user not admin ?>
        <div class="clearfix"></div>
    </header>

    <nav class="content-wrapper">
        <!-- Responsive Layout link -->
        <a href="#" id="nav-response-btn" data-open=".main-nav">
            <span class="glyphicon ion-navicon"></span>
        </a>
        
        <ul class="nav nav-inline main-nav">
            <li class="home"><a href="#">Home</a></li>
            <?php echo $tTheme->get_page_navigation("main"); ?>
        </ul>
    </nav>
</div>