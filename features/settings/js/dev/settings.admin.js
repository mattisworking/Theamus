function change_settings_tab() {
    $('[name="settings-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-settings', $(this).attr('data-file'));
        change_admin_window_title('theamus-settings', $(this).attr('data-title'));
    });
}


function change_path(to) {
    // Hide the old element
    $('#'+$('#type').val()+'-wrapper').hide();

    // Update the type element
    $('#type').val(to);

    // Show the new element
    $('#'+to+'-wrapper').show();

    show_appropriate_type_messages();
    resize_admin_window();
}

function show_appropriate_type_messages() {
    var type = $("#type").val();
    if (type === "custom" && $("#reqlogin")[0].checked) {
        $("#custom-wrapper").hide();
        $("#no-custom").show();
    } else

    if (type === "session" && $("#reqlogin")[0].checked) {
        $("#session-wrapper").hide();
        $("#no-session").show();
    } else {
        $("#no-session").hide();
    }

    if (type === "session" && $("#setting-session").val() === '') {
        $("#session-wrapper").show();
        $("#unsetsession").hide();
    }

    if (type === "session" && $("#setting-session").val() === 'true') {
        $("#session-wrapper").hide();
        $("#unsetsession").show();
    }

    if (type === 'require-login' && ($('#setting-session').val() === '' || $('#setting-session').val() === 'false')) {
        $("#require-login-wrapper").show();
        $("#unsetsession").hide();
    } else if (type === 'require-login' && ($('#setting-session').val() === 'in' || $('#setting-session').val() === 'out')) {
        $("#require-login-wrapper").hide();
        $("#unsetsession").show();
    }
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

    if ($('#reqlogin')[0].checked && type === 'session' || type === 'require-login') {
        ret += 'after-type="page";id="1"';
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


function load_feature_files_select() {
    // Make the call to get the pages options
    Theamus.Ajax.api({
        type: 'get',
        url: Theamus.base_url+'/settings/',
        method: ['Settings', 'get_feature_files_select'],
        data: { custom: { feature: $('#features-select').val() } },
        success: function(data) {
            if (data.error.status === 1) {
                $('#feature-files-select').html('<option>'+data.error.message+'</option>');
            } else {
                $('#feature-files-select').html(data.response.data);
            }
        }
    });
}


function set_session_page(type) {
    $('#session-notify').show();
    $('#setting-session').val(type);
    $('#setsesstype').text(type);
    $('#sessSave')[0].setAttribute('onclick', 'save_session("' + type + '")');
    change_path('page');
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
    change_path('session');
    cancel_session_set();
    $('#sessionsAreSet').hide();

    return false;
}


function customize() {
    // Handle the changing of path types
    $('[name="type"]').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.
        change_path($(this).attr('data-for'));
    });

    $('#features-select').change(function() {
        load_feature_files_select();
    });

    $('#reqlogin').change(function() {
        if (this.checked) {
            $("#login-notify").show();
            $("#required-login").val("true");
        } else {
            $("#login-notify").hide();
            $("#required-login").val("false");
        }
    });

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

    $('#customize-settings-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Notify the user the form was submitted and show them
        $('#customize-result').html(Theamus.Notify('spinner', 'Saving...'));
        $('#theamus-settings').scrollTop(0);

        // Disable the submit button
        $(this).find('button[type="submit"]').attr('disbaled', 'disabled');

        // Compile the homepage information and add it to the form
        $(this).append(compile_home());

        // Make the call to save the information
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/settings/',
            method: ['Settings', 'save_customization'],
            data: { form: this },
            success: function(data) {
                $('#customize-result').show();

                if (data.error.status === 1) {
                    $('#customize-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#customize-result').html(Theamus.Notify('success', 'Saved.'));

                    setTimeout(function() { $('#customize-result').html('').hide(); }, 2000);
                }

                $('#customize-settings-form').find('button[type="submit"]').removeAttr('disabled');
            }
        });
    });


    return; // Return!
}


function settings() {
    $('#config-email').change(function() {
        this.checked ? $('#email-container').show() : $('#email-container').hide();
        resize_admin_window();
    });

    $('#edit-settings-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.

        // Notify the user that the form was submitted and show them
        $('#settings-result').html(Theamus.Notify('spinner', 'Saving...'));
        $('#theamus-settings').scrollTop(0);

        // Disable the submit button
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        // Make the call to save the information
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/settings/',
            method: ['Settings', 'save_settings'],
            data: { form: this },
            success: function(data) {
                $('#settings-result').show();

                if (data.error.status === 1) {
                    $('#settings-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#settings-result').html(Theamus.Notify('success', 'Saved.'));

                    setTimeout(function() { $('#customize-result').html('').hide(); }, 2000);
                }

                $('#edit-settings-form').find('button[type="submit"]').removeAttr('disabled');
            }
        });
    });

    $('#update').click(function() {
        update_admin_window_content('theamus-settings', '/settings/update-check/');
        change_admin_window_title('theamus-settings', 'Theamus Auto Updater');
    });

    return; // Return!
}


function update_check() {
    $('#checker-wrapper').html(Theamus.Notify('spinner', 'Checking for updates...'));

    Theamus.Ajax.api({
        type: 'post',
        url: Theamus.base_url+'/settings/',
        method: ['Settings', 'get_update_info'],
        success: function(data) {
            if (data.error.status === 1) {
                $("#checker-wrapper").html(Theamus.Notify("danger", data.error.message));
            } else if (data.response.data.needsUpdate === 1) {
                var link = '<button class="btn btn-primary" id="settings_update-link">Update to the latest version of Theamus</button><hr class="form-split">';
                $('#checker-wrapper').html('<div class="col-12">'+link+data.response.data.updateNotes+'</div>');
                
                var updateInformation = data.response.data;

                $('#settings_update-link').click(function(e) {
                    e.preventDefault();

                    $('#auto-update-result').html(Theamus.Notify('spinner', 'Updating...'));

                    setTimeout(function() {
                        Theamus.Ajax.api({
                            type: 'post',
                            url: Theamus.base_url+'/settings/',
                            method: ['Settings', 'auto_update'],
                            data: { custom: updateInformation },
                            success: function(dd) {
                                if (dd.error.status === 1) {
                                    $('#auto-update-result').html(Theamus.Notify('danger', dd.error.message));
                                } else {
                                    $('#auto-update-result').html(Theamus.Notify('success', 'Everything went smoothly.  In order for things to take effect, you need to <a id="refresh">refresh the page</a>.'));
                                    document.getElementById("refresh").addEventListener("click", function(e) {
                                        e.preventDefault();
                                        update_admin_window_content('theamus-settings', '/settings/');
                                        change_admin_window_title('theamus-settings', 'Theamus Settings');
                                        window.location.reload(true);
                                    });
                                }
                            }
                        });
                    }, 10); // short delay so the spinner starts
                });
            } else {
                $('#checker-wrapper').html(Theamus.Notify('info', 'No updates are available.'));
            }
        }
    });

    return; // Return!
}

function settings_manual_update() {
    $('#settings_update-form').submit(function(e) {
        e.preventDefault();

        $('#settings_update-result').show().html(Theamus.Notify('spinner', 'Updating...'));
        $('#theamus-settings').scrollTop(0);

        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/settings/',
            method: ['Settings', 'manual_update'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#settings_update-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#settings_update-result').html(Theamus.Notify('success', 'Everything went smoothly.  In order for things to take effect, you need to <a href="./">refresh the page</a>.'));
                }
                $('#settings_update-form').find('button[type="submit"]').removeAttr('disabled');
            }
        });
    });
}

function test_email_listener() {    
    $("#settings_test-email-button").on("click", function(e) {
        $("#settings_test-email-result").show().html(Theamus.Notify("spinner", "Sending test email..."));
        
        setTimeout(function() {
            Theamus.Ajax.api({
                type: "post",
                url: Theamus.base_url + "/settings/",
                method: ["Settings", "test_email"],
                data: {
                    custom: {
                        host: $("#host").val(),
                        protocol: $("#protocol").val(),
                        port: $("#port").val(),
                        email: $("#email").val(),
                        password: $("#password").val(),
                        to: $("#settings_test-email").val()
                    }
                },
                success: function(data) {
                    if (data.error.status === 1) {
                        $("#settings_test-email-result").html(Theamus.Notify("danger", data.error.message.replace(/(<([^>]+)>)/ig,"")));
                        setTimeout(function() { $("#settings_test-email-result").html("").hide(); }, 1500);
                    } else {
                        $("#settings_test-email-result").html(Theamus.Notify("success", "Email sent successfully."));
                        setTimeout(function() { $("#settings_test-email-result").html("").hide(); }, 5000);
                    }
                }
            });
        }, 100);
    });
}