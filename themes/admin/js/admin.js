function admin_add_extras() {
    var scripts = $('[name="addscript"]'),
        styles = $('[name="addstyle"]');

    for (var i = 0; i < scripts.length; i++) {
        add_js_file(scripts[i].value);
        $(scripts[i]).remove();
    }

    for (var i = 0; i < styles.length; i++) {
        add_css(styles[i].value);
        $(styles[i]).remove();
    }
}

function admin_window_run_on_load(func) {
    if (typeof(window[func]) === 'function') {
        return window[func]();
    } else {
        setTimeout(function() {
            admin_window_run_on_load(func);
        }, 100);
    }
}

function create_admin_window(window_id, window_title, window_url) {
    if ($('#'+window_id).length > 0) {
        bring_admin_window_to_front($('#'+window_id).parentsUntil('.admin-windows'));
        return false;
    }

    if (Theamus.Mobile === false) {
        if ($('.admin-navigation').hasClass('admin-navigation-left')) {
            $('.admin-navigation').addClass('admin-navigation-open-'+admin_position);
        } else {
            $('.admin-navigation').addClass('admin-navigation-open-'+admin_position);
        }
    }

    var ad_window = document.createElement('div');
    $(ad_window).addClass('admin-window');
    $(ad_window).addClass('admin-window-init');
    if (Theamus.Mobile === true) {
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
    $('#'+window_id).parentsUntil('.admin-windows').removeClass('admin-window-maxheight');

    bring_admin_window_to_front($('#'+window_id).parentsUntil('.admin-windows'));
    admin_window_loading(window_id);

    setTimeout(function() {
        Theamus.Ajax.run({
            url:        Theamus.base_url+url,
            type:       "include",
            result:     window_id,
            after:      function() {
                $('#'+window_id).parentsUntil('.admin-windows').find('.refresh').attr('data-url', url);
                admin_add_extras();
                resize_admin_window();
                if (resize !== null) {
                    resize = setInterval(resize_admin_window, 500);
                    setTimeout(function() { clearInterval(resize); resize = null; }, 10000);
                }
            }
        });
    }, 500);
}

function resize_admin_window() {
    for (var i = 0; i < $('.admin-window').length; i++) {
        var ad_window = $('.admin-window')[i];
        if ($(ad_window).height() > $(window).height() && Theamus.Mobile === false) {
            $(ad_window).addClass('admin-window-maxheight');
        }
    }
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
    if (Theamus.Mobile === false) {
        $('.admin-window').draggable({
            handle: '.window-chrome',
            cancel: '.close',
            containment: 'window',
            drag: function() {
                $(this).removeClass('admin-window-init');
            }
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

function switch_position_text() {
    $('[name="admin-move"]').html(admin_position === 'left' ? '>>>' : '<<<');
}

$(document).ready(function() {
    // Define the position of the administration panel
    if (localStorage.getItem('admin_position') === null) {
        admin_position = 'left';
        localStorage.setItem('admin_position', 'left');
    } else {
        admin_position = localStorage.getItem('admin_position');
    }

    if (admin_position !== 'left' && admin_position !== 'right') {
        admin_position = 'left';
        localStorage.setItem('admin_position', 'left');
    }

    $('.admin-header').addClass('admin-header-'+admin_position);
    $('.admin-navigation').addClass('admin-navigation-'+admin_position);

    resize = null;

    add_css('themes/admin/style/css/admin.css');

    if (Theamus.Mobile === true) {
        $('.admin').addClass('admin-mobile');
    }

    $('#ad_open-nav').click(function(e) {
        e.preventDefault();
        $('.admin-navigation').toggleClass('admin-navigation-open');
        if ($('.admin-navigation').hasClass('admin-navigation-'+admin_position)) {
            $('.admin-navigation').toggleClass('admin-navigation-open-'+admin_position);
        } else {
            $('.admin-navigation').toggleClass('admin-navigation-open-'+admin_position);
        }

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

        if (Theamus.Mobile === true) {
            $('.admin-navigation').removeClass('admin-navigation-open');
            $('.admin-navigation').removeClass('admin-navigation-open-left');
            $('.admin-navigation').removeClass('admin-navigation-open-right');
        }

        var window_title = $(this).attr('data-title'),
            window_id = $(this).attr('data-id'),
            window_url = $(this).attr('data-url');

        create_admin_window(window_id, window_title, window_url);

        for (var i = 0; i < $('.admin-window').length; i++) {
            if (Theamus.Mobile === false) {
                var ad_window_pos = $($('.admin-window')[i]).position(),
                    expected_left = 200 + (20 * i),
                    expected_top = 10 + (20 * i);

                if (ad_window_pos.left === expected_left && ad_window_pos.top === expected_top) {
                    $('#'+window_id).parentsUntil('.admin-windows').css({left: expected_left + 20 + 'px', top: expected_top + 20 + 'px'});
                }
            }
        }
    });

    $('[name="admin-move"]').click(function(e) {
        e.preventDefault();

        $('.admin-header').removeClass('admin-header-'+admin_position);
        $('.admin-navigation').removeClass('admin-navigation-'+admin_position);
        $('.admin-navigation').removeClass('admin-navigation-open-'+admin_position);

        var new_position = admin_position === 'left' ? 'right' : 'left';

        $('.admin-header').addClass('admin-header-'+new_position);
        $('.admin-navigation').addClass('admin-navigation-'+new_position);
        $('.admin-navigation').addClass('admin-navigation-open-'+new_position);

        localStorage.setItem('admin_position', new_position);
        admin_position = new_position;

        switch_position_text();
    });
    switch_position_text();

    setTimeout(function() {
        $('.admin').removeAttr('style');
        $('.admin').addClass('admin-on');
    }, 200);
});