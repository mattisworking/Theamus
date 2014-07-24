function type_listeners() {
    $("[name='type']").click(function (e) {
        e.preventDefault();
        var to = $(this).data("for");
        switch_type(to);
    });
}

function switch_type(to) {
    var old = $("#type").val();
    hide_all();
    $("#"+old+"-wrapper").hide();
    $("#"+to+"-wrapper").show();
    $("#type").val(to);
    load_feature_files();
    show_appropriate();
}

function hide_all() {
    $("#no-custom").hide();
    $("#no-session").hide();
    $("#unsetsession").hide();
}

function show_appropriate() {
    var type = $("#type").val();
    if (type === "custom" && $("#reqlogin")[0].checked) {
        $("#custom-wrapper").hide();
        $("#no-custom").show();
    }

    if (type === "session" && $("#reqlogin")[0].checked) {
        $("#session-wrapper").hide();
        $("#no-session").show();
    }

    if (type === "session" && $("#setting-session").val() === "true") {
        $("#session-wrapper").hide();
        $("#unsetsession").show();
    }

    if (type === "require-login" && $("#setting-session").val() !== "") {
        $("#require-login-wrapper").hide();
        $("#unsetsession").show();
    }
}

function feature_files_listener() {
    $("[name='feature-id']").change(function() {
        load_feature_files();
    });
}

function load_feature_files() {
    var t = $("#type").val();
    if (t === "feature") {
        theamus.ajax.run({
            url:    "settings/feature-files&id="+$("[name='feature-id']")[0].value,
            result: "feature-file-list",
            type:   "include"
        });
    }
}

function updateFeatureFiles(feature) {
    // Run the ajax
    theamus.ajax.run({
       url: 'settings/feature-file-list/&id=' + feature,
       result : 'feature-file-list'
    });

    // Do nothing
    return false;
}

function toggle_notify_login() {
    $("#reqlogin").change(function() {
        login_notify();
    });
}
function login_notify() {
    if ($("#reqlogin")[0].checked) {
        $("#login-notify").show();
        $("#required-login").val("true");
    } else {
        $("#login-notify").hide();
        $("#required-login").val("false");
    }
}

function set_session_page(type) {
    $('#session-notify').show();
    $('#setting-session')[0].value = type;
    $('#setsesstype').text(type);
    $('#sessSave')[0].setAttribute('onclick', 'save_session("' + type + '")');
    switch_type('page');
}

function cancel_session_set(saved) {
    $('#session-notify').hide();
    var saved = saved !== undefined ? saved : 'false';
    $('#setting-session')[0].value = saved;
}

function save_session(io) {
    var type = $('#type').val(),
        elements = $("#"+type+"-wrapper :input"),
        ba = io === "in" ? "after" : "before";

    if (type !== "session" && type !== "login") {
        var io_val = ba+"-type=\""+type+"\";";
        if (type === "page") io_val += ba+"-id=\""+elements[0].value+"\";";

        if (type === "feature") {
            io_val += ba+"-id=\""+elements[0].value+"\";";
            io_val += ba+"-file=\""+elements[1].value+"\";";
        }

        if (type === "custom") io_val += ba+"-url=\""+elements[0].value+"\";";

        $("#"+io).val(io_val);

        if (io === 'in') {
            cancel_session_set();
            set_session_page('out');
        } else {
            cancel_session_set('true');
            $('#sessionsAreSet').show();
        }
    }
}

function reset_session() {
    $('#setting-session').val("");
    $('#in').val("");
    $('#out').val("");
    switch_type('session');
    $('#sessionsAreSet').hide();

    return false;
}

function compile_home() {
    var type = $("#type").val(),
        elements = $("#"+type+"-wrapper :input"),
        t, ret = "{t:homepage;";

    if ($("#setting-session").val() === "true") {
        ret += "type=\"session\";";
        type = "session";
    } else if ($("#reqlogin")[0].checked) {
        ret += "type=\"require-login\";";
        t = "require-login";
    } else {
        ret += "type=\""+type+"\";";
        t = type;
    }

    if (type === "page") {
        if (t === "require-login") ret += "after-type=\"page\";";
        ret += "id=\""+elements[0].value+"\";";
    }

    if (type === "feature") {
        if (t === "require-login") ret += "after-type=\"feature\";";
        ret += "id=\""+elements[0].value+"\";file=\""+elements[1].value+"\";";
    }

    if (type === "custom") ret += "url=\""+elements[0].value+"\"";

    if (type === "session") ret += $("#in").val()+$("#out").val();

    ret += ":}";

    var el = document.createElement("input");
    el.setAttribute("type", "hidden");
    el.setAttribute("name", "home-page");
    el.value = ret;

    return el;
}

function change_settings_tab() {
    $('[name="settings-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-settings', $(this).attr('data-file'));
        change_admin_window_title('theamus-settings', $(this).attr('data-title'));
    });
}

$(document).ready(function() {
    load_feature_files();
    login_notify();
    toggle_notify_login();
    type_listeners();
    feature_files_listener();

    $("#set-sessions").click(function(e) {
        e.preventDefault();
        set_session_page("in");
    });

    $("[name='reset-sessions']").click(function(e) {
        e.preventDefault();
        reset_session();
    });

    $("#cancel-sessions").click(function(e) {
        e.preventDefault();
        cancel_session_set();
    });

    $("#custom-form").submit(function(e) {
        e.preventDefault();
        this.appendChild(compile_home());
        theamus.ajax.run({
            url:            "settings/save-customization/",
            result:         "custom-result",
            form:           "custom-form",
            hide_result:    3
        });
    });
});