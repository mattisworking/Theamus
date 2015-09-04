function admin_add_extras() {
    var scripts = $('[name="addscript"]'),
        styles = $('[name="addstyle"]'),
        dataforce = undefined,
        force = undefined;

    for (var i = 0; i < scripts.length; i++) {
        dataforce = scripts[i].getAttribute('data-force');
        force = false;
    
        if (dataforce !== null) {
            force = dataforce === "false" ? false : true;
        }
        
        add_js_file(scripts[i].value, force);
        $(scripts[i]).remove();
    }
    
    dataforce = force = undefined;

    for (var i = 0; i < styles.length; i++) {
        dataforce = styles[i].getAttribute('data-force');
        force = false;
    
        if (dataforce !== null) {
            force = dataforce === "false" ? false : true;
        }
    
        add_css(styles[i].value, force);
        $(styles[i]).remove();
    }
}

function get_window_function(functionName) {
    if (window === undefined) return 0;
    if (functionName === undefined) return 0;
    
    if (functionName.indexOf(".") > 0) {
        var functionNameArray = functionName.split("."),
            windowFunction = window;

        for (var i = 0; i < functionNameArray.length; i++) {
            if (windowFunction[functionNameArray[i]] === undefined) return 0;
            windowFunction = windowFunction[functionNameArray[i]];
        }

        return windowFunction;
    }
    
    if (typeof(window[functionName]) === "function") {
        return window[functionName];
    }
    
    return 0;
}

loading = {};

function admin_window_run_on_load(func) {
    var window_function = get_window_function(func);
    loading[func] = 'loading';
    
    if (typeof(window_function) === 'function') {
        var ran = window_function();
        loading[func] = ran;
        return ran;
    } else {
        setTimeout(function() {
            admin_window_run_on_load(func);
        }, 100);
    }
}

function create_admin_window(window_id, window_title, window_url, window_forceopen, pre_style) {
    if (!pre_style) pre_style = "";

    var theamus_ls = JSON.parse(localStorage.getItem("Theamus"));

    if ($('#'+window_id).length > 0) {
        bring_admin_window_to_front($('#'+window_id).parentsUntil('.admin-windows'));
        return false;
    }

    if (theamus_ls['admin_open'] === true || window_forceopen === '1') {
        $('.admin').addClass('admin-panel-open');
        $('.admin-header').addClass('admin-header-on');
        $('.admin-navigation').addClass('admin-navigation-'+admin_position)
        $('.admin-navigation').addClass('admin-navigation-open');
        if (Theamus.Mobile === false) {
            if ($('.admin-navigation').hasClass('admin-navigation-left')) {
                $('.admin-navigation').addClass('admin-navigation-open-'+admin_position);
            } else {
                $('.admin-navigation').addClass('admin-navigation-open-'+admin_position);
            }
        }
    }

    var ad_window = document.createElement('div');
    $(ad_window).addClass('admin-window');
    (pre_style === "") ? $(ad_window).addClass('admin-window-init') : $(ad_window).attr("style", pre_style);
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

    var ad_content_inner = document.createElement('div');
    $(ad_content_inner).addClass('admin_window-inner-content');
    $(ad_content).append(ad_content_inner);
    
    var ad_content_loader = document.createElement('div');
    $(ad_content_loader).addClass('admin_window-loader');
    $(ad_content).append(ad_content_loader);

    $('.admin-windows').append(ad_window);

    setTimeout(function() {
        if (theamus_ls['admin_open'] === true || window_forceopen === '1') {
            $(ad_window).addClass('admin-window-open');
        }
        admin_window_listeners();
        update_admin_window_content(window_id, window_url);
    }, 200);

    if (pre_style === "") {
        theamus_ls['admin_cache'][window_id] = [window_title, window_url, ""];
        localStorage.setItem('Theamus', JSON.stringify(theamus_ls));
    }
}

function admin_window_loading(window_id) {
    $('#'+window_id).children("div.admin_window-loader").show().html('<span class=\'spinner spinner-fixed admin-window-spinner\'></span>');
}

function show_admin_window_content(window_id) {
    var gb = [];
    
    for (var key in loading) {
       if (loading[key] === 'loading') gb.push(false);
       else gb.push(true);
    }
    
    if (gb.indexOf(false) > -1) {
        setTimeout(function() { show_admin_window_content(window_id); }, 100);
    } else {
        $("#"+window_id).children("div.admin_window-inner-content").show();
        $('#'+window_id).children("div.admin_window-loader").hide();
    }
    return;
}

function update_admin_window_content(window_id, url) {
    var theamus_ls = JSON.parse(localStorage.getItem("Theamus"));
    if (theamus_ls['admin_open'] === true) {
        $('.admin').addClass("admin-panel-open");
    }
    $('#'+window_id).parentsUntil('.admin-windows').removeClass('admin-window-maxheight');

    bring_admin_window_to_front($('#'+window_id).parentsUntil('.admin-windows'));
    admin_window_loading(window_id);

    Theamus.Ajax.run({
        url:        Theamus.base_url+url,
        type:       "include",
        result:     $("#"+window_id).children("div.admin_window-inner-content"),
        after:      function() {
            $("#"+window_id).children("div.admin_window-inner-content").hide();
            $('#'+window_id).parentsUntil('.admin-windows').find('.refresh').attr('data-url', url);
            admin_add_extras();
            resize_admin_window();
            
            show_admin_window_content(window_id);
        },
        fail: function(status) {
            if (status === 404) {
                $("#"+window_id).children("div.admin_window-inner-content").hide();
                $('#'+window_id).parentsUntil('.admin-windows').find('.refresh').attr('data-url', url);
                
                $("#"+window_id).children("div.admin_window-inner-content").html("<div style='padding:10px;font-family: monospace;'>(> ^-^ )> &nbsp;&nbsp;|&nbsp;&nbsp; 404 not found.</div>").show();
                $('#'+window_id).children("div.admin_window-loader").hide();
            }
        }
    });

    var theamus_ls = JSON.parse(localStorage.getItem("Theamus"));
    if (theamus_ls['admin_cache'][window_id] !== undefined) {
        if (theamus_ls['admin_cache'][window_id][1] !== url) {
            theamus_ls['admin_cache'][window_id][1] = url;
            localStorage.setItem('Theamus', JSON.stringify(theamus_ls));
        }
    }
    
    loading = {};
}

function resize_admin_window() {
    if (resize !== null) clearInterval(resize);

    for (var i = 0; i < $('.admin-window').length; i++) {
        var ad_window = $('.admin-window')[i];
        
        if (($(ad_window).height() > $(window).height()) && Theamus.Mobile === false) {
            $(ad_window).addClass('admin-window-maxheight');
        } else if (($(ad_window).find(".window-content").children("div").height() < parseInt(($(window).height() * .90) - 100)) && Theamus.Mobile === false) {
            $(ad_window).removeClass('admin-window-maxheight');
        }
    }

    resize = setInterval(function() { resize_admin_window(); }, 100);
}

function change_admin_window_title(window_id, title) {
    var chrome_title = $($('#'+window_id).siblings()[0]).children('.title');
    chrome_title.attr('title', title);
    chrome_title.html(title);

    var theamus_ls = JSON.parse(localStorage.getItem("Theamus"));
    if (theamus_ls['admin_cache'][window_id] !== undefined) {
        if (theamus_ls['admin_cache'][window_id][0] !== title) {
            theamus_ls['admin_cache'][window_id][0] = title;
            localStorage.setItem('Theamus', JSON.stringify(theamus_ls));
        }
    }
}

function bring_admin_window_to_front(ad_window) {
    for (var i = 0; i < $('.admin-window').length; i++) {
        if ($('.admin-window')[i] !== ad_window) {
            $($('.admin-window')[i]).css('z-index', false);
        }
    }
    $(ad_window).css('z-index', '50');
}

function admin_window_listeners() {
    if (Theamus.Mobile === false) {
        $('.admin-window').draggable({
            handle: '.window-chrome',
            cancel: '.close',
            containment: 'window',
            start: function() {
                bring_admin_window_to_front(this);
            },
            drag: function() {
                $(this).removeClass('admin-window-init');
            },
            stop: function() {

                var window_id = $(this).find(".window-content").attr("id"),
                    theamus_ls = JSON.parse(localStorage.getItem("Theamus"));
                if (theamus_ls['admin_cache'][window_id] !== undefined) {
                    if (theamus_ls['admin_cache'][window_id][2] !== $(this).attr("style")) {
                        theamus_ls['admin_cache'][window_id][2] = $(this).attr("style");
                        localStorage.setItem('Theamus', JSON.stringify(theamus_ls));
                    }
                }
            }
        });
    }

    $('.close-admin-window').click(function(e) {
        e.preventDefault();
        var ad_window = $(this);
        ad_window.parentsUntil('.admin-windows').addClass("admin-window-closing");

        var window_id = ad_window.parentsUntil('.admin-windows').find(".window-content").attr("id");

        var theamus_ls = JSON.parse(localStorage.getItem("Theamus"));
        if (theamus_ls['admin_cache'][window_id] !== undefined) {
            delete theamus_ls['admin_cache'][window_id];
            localStorage.setItem('Theamus', JSON.stringify(theamus_ls));
        }

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
    if (localStorage.getItem("Theamus") === null) {
        localStorage.setItem("Theamus", JSON.stringify({"admin_position": "left", "admin_cache": {1:0}, "admin_open": false}));
    }

    var theamus_ls = JSON.parse(localStorage.getItem("Theamus"));

    admin_position = theamus_ls['admin_position'];
    if (admin_position !== 'left' && admin_position !== 'right') {
        theamus_ls['admin_position'] = "left";
        localStorage.setItem('Theamus', JSON.stringify(theamus_ls));
    }

    $('.admin-header').addClass('admin-header-'+admin_position);
    $('.admin-navigation').addClass('admin-navigation-'+admin_position);

    resize = null;

    if (Theamus.Mobile === true) {
        $('.admin').addClass('admin-mobile');
    }

    $('#ad_open-nav').click(function(e) {
        e.preventDefault();

        var ad_open = false;

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

            ad_open = true;
        } else {
            $('.admin-header').removeClass('admin-header-on');
            $('.admin').removeClass('admin-panel-open');

            for (var i = 0; i < $('.admin-window').length; i++) {
                $($('.admin-window')[i]).removeClass('admin-window-open');
            }

            ad_open = false;
        }

        var theamus_ls = JSON.parse(localStorage.getItem("Theamus"));
        theamus_ls['admin_open'] = ad_open;
        localStorage.setItem('Theamus', JSON.stringify(theamus_ls));
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
            window_url = $(this).attr('data-url')
            window_forceopen = $(this).attr('data-forceopen');

        create_admin_window(window_id, window_title, window_url, window_forceopen);

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

        theamus_ls['admin_position'] = new_position;
        localStorage.setItem('Theamus', JSON.stringify(theamus_ls));
        admin_position = new_position;

        switch_position_text();
    });
    switch_position_text();


    add_css('themes/admin/style/css/admin.css');
    document.querySelector("link[href='themes/admin/style/css/admin.css']").addEventListener("load", function() {
        $('.admin').removeAttr('style');
        $('.admin').addClass('admin-on');
    });

    if (theamus_ls['admin_cache'] !== undefined && Theamus.Mobile === false) {
        for (var key in theamus_ls['admin_cache']) {
            if (key === "1") continue;
            create_admin_window(key, theamus_ls['admin_cache'][key][0], theamus_ls['admin_cache'][key][1], theamus_ls['admin_cache'][key][2]);
        }
    }
});