<div class='admin' style='opacity: 0;'>
    <div class='admin-header'>
        <ul class='nav nav-inline'>
            <li class='admin-logo'>
                <a href=''><img src='themes/admin/img/theamus-logo.svg' alt='t'></a>
                <ul>
                    <li><a href='' name='admin-nav-item' data-id='about-theamus' data-title='About Theamus' data-url='settings/about-theamus/'>About Theamus</a></li>
                    <li class='nav-split'></li>
                    <li><a href='http://www.theamus.com' target='_blank'>Theamus Website</a></li>
                    <li><a href='http://www.theamus.com/wiki/' target='_blank'>Documentation</a></li>
                </ul>
            </li>
            <li><a href='#' id='ad_open-nav'><span class='glyphicon ion-navicon'></span></a></li>
            <li>
                <a href=''><span class='glyphicon ion-person'></span></a>
                <ul>
                    <li><a href='accounts/user/edit-account/'>Edit Profile</a></li>
                    <li class='nav-split'></li>
                    <li><a href='javascript:user_logout();'>Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <div class='admin-navigation'>
        <ul class='nav nav-vertical'>
            <li>
                <a href='#' name='admin-nav-item' data-id='theamus-dashboard' data-title='Theamus Dashboard' data-url='default/adminHome/'>
                    <span class='glyphicon ion-speedometer'></span>
                    <span class='text'>Dashboard</span>
                </a>
            </li>
            <li>
                <a href='' name='admin-nav-item' data-id='theamus-accounts' data-title='Theamus Accounts' data-url='accounts/'>
                    <span class='glyphicon ion-person-stalker'></span>
                    <span class='text'>Accounts</span>
                </a>
            </li>
            <li>
                <a href='' name='admin-nav-item' data-id='theamus-groups' data-title='Theamus Groups' data-url='groups/'>
                    <span class='glyphicon ion-ios7-people'></span>
                    <span class='text'>Groups</span>
                </a>
            </li>
            <li>
                <a href='' name='admin-nav-item' data-id='theamus-media' data-title='Theamus Media' data-url='media/'>
                    <span class='glyphicon ion-ios7-camera'></span>
                    <span class='text'>Media</span>
                </a>
            </li>
            <li>
                <a href='' name='admin-nav-item' data-id='theamus-pages' data-title='Theamus Pages' data-url='pages/'>
                    <span class='glyphicon ion-document-text'></span>
                    <span class='text'>Pages</span>
                </a>
            </li>
            <li>
                <a href='' name='admin-nav-item' data-id='theamus-navigation' data-title='Theamus Navigation' data-url='navigation/'>
                    <span class='glyphicon ion-link'></span>
                    <span class='text'>Navigation</span>
                </a>
            </li>
            <li>
                <a href='' name='admin-nav-item' data-id='theamus-features' data-title='Theamus Features' data-url='features/'>
                    <span class='glyphicon ion-grid'></span>
                    <span class='text'>Features</span>
                </a>
            </li>
            <li>
                <a href='' name='admin-nav-item' data-id='theamus-appearance' data-title='Theamus Appearance' data-url='appearance/'>
                    <span class='glyphicon ion-eye'></span>
                    <span class='text'>Appearance</span>
                </a>
            </li>
            <li>
                <a href='' name='admin-nav-item' data-id='theamus-settings' data-title='Theamus Settings' data-url='settings/'>
                    <span class='glyphicon ion-ios7-gear'></span>
                    <span class='text'>Settings</span>
                </a>
            </li>
        </ul>
    </div>

    <div class='admin-windows'></div>
</div>

<script>
    function create_admin_window(window_id, window_title, window_url) {
        if ($('#'+window_id).length > 0) {
            bring_admin_window_to_front($('#'+window_id).parentsUntil('.admin-windows'));
            return false;
        }

        if (theamus.mobile === false) {
            $('.admin-navigation').addClass('admin-navigation-open');
        }

        var ad_window = document.createElement('div');
        $(ad_window).addClass('admin-window');
        if (theamus.mobile === true) {
            $(ad_window).addClass('admin-window-mobile');
        }

        var ad_chrome = document.createElement('div');
        $(ad_chrome).addClass('window-chrome');
        $(ad_chrome).html('<span class=\'title\' title=\''+window_title+'\'>'+window_title+'</span>');
        $(ad_chrome).append('<span class=\'close close-admin-window\'><a href=\'#\' title=\'Close Window\'><span class=\'ion-close\'></span></a></span>');
        $(ad_chrome).append('<span class=\'refresh refresh-admin-window\' data-url=\''+window_url+'\'><a href=\'#\' title=\'Refresh Window\'><span class=\'ion-refresh\'></span></a></span>');
        $(ad_window).append(ad_chrome);

        var ad_content = document.createElement('div');
        $(ad_content).addClass('window-content');
        $(ad_content).attr('id', window_id);
        $(ad_window).append(ad_content);

        $('.admin-windows').append(ad_window);

        setTimeout(function() {
            $(ad_window).addClass('admin-window-open');
            admin_window_listeners();
            update_admin_window_content(window_id, window_url);
        }, 200);
    }

    function admin_window_loading(window_id) {
        $('#'+window_id).html('<span class=\'spinner spinner-fixed admin-window-spinner\'></span>');
    }

    function update_admin_window_content(window_id, url) {
        $('.admin').addClass('admin-panel-open');

        bring_admin_window_to_front($('#'+window_id).parentsUntil('.admin-windows'));
        admin_window_loading(window_id);

        setTimeout(function() {
            theamus.ajax.run({
                url:        theamus.base_url+url,
                type:       "include",
                result:     window_id,
                after:      function() {
                    setTimeout(function() {
                        for (var i = 0; i < $('.admin-window').length; i++) {
                            var ad_window = $('.admin-window')[i];
                            if ($(ad_window).height() > $(window).height() && theamus.mobile === false) {
                                $(ad_window).addClass('admin-window-maxheight');
                            }
                        }
                    }, 500);
                }
            });
        }, 500);
    }

    function change_admin_window_title(window_id, title) {
        var chrome_title = $($('#'+window_id).siblings()[0]).children('.title');
        chrome_title.attr('title', title);
        chrome_title.html(title);
    }

    function bring_admin_window_to_front(ad_window) {
        for (var i = 0; i < $('.admin-window').length; i++) {
            if ($('.admin-window')[i] !== ad_window) {
                $($('.admin-window')[i]).css('z-index', false);
            }
        }
        $(ad_window).css('z-index', '10');
    }

    function admin_window_listeners() {
        if (theamus.mobile === false) {
            $('.admin-window').draggable({
                handle: '.window-chrome',
                cancel: '.close',
                containment: 'window'
            });
        }

        $('.close-admin-window').click(function(e) {
            e.preventDefault();
            var ad_window = $(this);
            ad_window.parentsUntil('.admin-windows').addClass("admin-window-closing");

            setTimeout(function() {
                ad_window.parentsUntil('.admin-windows').remove();

                if ($('.admin-window').length === 0) {
                    $('.admin').removeClass('admin-panel-open');
                }
            }, 1000);
        });

        $('.refresh-admin-window').click(function(e) {
            e.preventDefault();

            var window_id = $($(this).parent().siblings('.window-content')[0]).attr('id');
            update_admin_window_content(window_id, $(this).attr('data-url'));
        });

        $('.admin-window').click(function(e) {
            bring_admin_window_to_front(this);
        });
    }

    $(document).ready(function() {
        add_css('themes/admin/style/css/admin.css');

        if (theamus.mobile === true) {
            $('.admin').addClass('admin-mobile');
        }

        $('#ad_open-nav').click(function(e) {
            e.preventDefault();
            $('.admin-navigation').toggleClass('admin-navigation-open');

            if ($('.admin-navigation').hasClass('admin-navigation-open')) {
                $('.admin-header').addClass('admin-header-on');
                $('.admin').addClass('admin-panel-open');

                for (var i = 0; i < $('.admin-window').length; i++) {
                    $($('.admin-window')[i]).addClass('admin-window-open');
                }
            } else {
                $('.admin-header').removeClass('admin-header-on');
                $('.admin').removeClass('admin-panel-open');

                for (var i = 0; i < $('.admin-window').length; i++) {
                    $($('.admin-window')[i]).removeClass('admin-window-open');
                }
            }
        });


        $('[name="admin-nav-item"]').click(function(e) {
            e.preventDefault();

            if (theamus.mobile === true) {
                $('.admin-navigation').removeClass('admin-navigation-open');
            }

            var window_title = $(this).attr('data-title'),
                window_id = $(this).attr('data-id'),
                window_url = $(this).attr('data-url');

            create_admin_window(window_id, window_title, window_url);

            for (var i = 0; i < $('.admin-window').length; i++) {
                if (theamus.mobile === false) {
                    var ad_window_pos = $($('.admin-window')[i]).position(),
                        expected_left = 200 + (20 * i),
                        expected_top = 10 + (20 * i);

                    if (ad_window_pos.left === expected_left && ad_window_pos.top === expected_top) {
                        $('#'+window_id).parentsUntil('.admin-windows').css({left: expected_left + 20 + 'px', top: expected_top + 20 + 'px'});
                    }
                }
            }
        });

        setTimeout(function() {
            $('.admin').removeAttr('style');
            $('.admin').addClass('admin-on');
        }, 200);
    });
</script>