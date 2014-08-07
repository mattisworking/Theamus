function login() {
    // Listen for the login form to be submitted
    $('#login-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Make the call to log the user in
        Theamus.Ajax.api({
            type:   'get',
            url:    Theamus.base_url+'/accounts/',
            method: ['Accounts', 'login'],
            data: {
                form: $('#login-form')
            },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#login-result').html(Theamus.Notify('danger', data.error.message));
                } else if (data.response.data === true) {
                    window.location=$("#redirect_url").val();
                }
            }
        });
    });
}


function change_glyph(e, t, n) {
    $('#'+e+' .glyphicon').removeClass('ion-checkmark');
    $('#'+e+' .glyphicon').removeClass('ion-close');
    $('#'+e+' .glyphicon').attr('title', n);
    if (t !== false) $('#'+e+' .glyphicon').addClass(t);
}


function register() {
    // Username keyup for check
    $('#username').keyup(function () {
        // Remove any glyphs for an empty value
        if ($(this).val() === '') change_glyph('username-group', false, '');
        else {
            // Make the call to get the information
            Theamus.Ajax.api({
                type:   'get',
                method: ['Accounts', 'check_username'],
                url:    Theamus.base_url+'/accounts/',
                data: {
                    custom: { username: $(this).val() }
                },
                success: function (data) {
                    if (data.error.status === 1) {
                        change_glyph('username-group', 'ion-close', data.error.message);
                    } else {
                        change_glyph('username-group', 'ion-checkmark', '');
                    }
                }
            });
        }
    });

    // Password keyup for check
    $('#password').keyup(function () {
        if ($(this).val() === '') change_glyph('password-group', false, '');
        else {
            // Make the call to check the password
            Theamus.Ajax.api({
                type:   'get',
                method: ['Accounts', 'check_password'],
                url:    Theamus.base_url+'/accounts/',
                data: {
                    custom: { password: $(this).val() }
                },
                success: function (data) {
                    if (data.error.status === 1) {
                        change_glyph('password-group', 'ion-close', data.error.message);
                    } else {
                        change_glyph('password-group', 'ion-checkmark', '');
                    }
                }
            });
        }
    });

    // Password repeat keyup check
    $('#password-again').keyup(function () {
        if ($(this).val() === '') {
            change_glyph('password-repeat-group', false, '');
        } else if ($(this).val() !== $('#password').val()) {
            change_glyph('password-repeat-group', 'ion-close', 'The passwords do not match.');
        } else if ($(this).val() === $('#password').val()) {
            change_glyph('password-repeat-group', 'ion-checkmark', '');
        }
    });

    // Email keyup check
    $('#email').keyup(function () {
        if ($(this).val() === '') change_glyph('email-group', false, '');
        else {
            // Make the call to get the informaiton
            Theamus.Ajax.api({
                type:   'get',
                method: ['Accounts', 'check_email'],
                url:   Theamus.base_url+'/accounts/',
                data: {
                    custom: { email: $(this).val() }
                },
                success: function (data) {
                    if (data.error.status === 1) {
                        change_glyph('email-group', 'ion-close', data.error.message);
                    } else {
                        change_glyph('email-group', 'ion-checkmark', '');
                    }
                }
            });
        }
    });

    // Registration form submission
    $('#register-form').submit(function (e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Notify the user that the registration is going on
        $('#register-result').html(Theamus.Notify('spinner', 'Registering...'));

        // Make the call to register the user
        Theamus.Ajax.api({
            type:   'post',
            method: ['Accounts', 'register_user'],
            url:    Theamus.base_url+'/accounts/',
            data: {
                form: $('#register-form')
            },
            success: function (data) {
                var reg_btn = $('#register-btn'); // Define the registration button

                // Disable the registration button
                reg_btn.attr('disabled', true);

                if (data.error.status === 1) {
                    $('#register-result').html(Theamus.Notify('danger', data.error.message));
                    reg_btn.attr('disabled', false);
                } else if (data.response.data === true) {
                    $('#register-result').html(Theamus.Notify('success', 'The account registration was successfull.  Check your email for an activation code!'));
                } else {
                    reg_btn.attr('disabled', false);
                    $('#register-result').html(Theamus.Notify('success', data.response.data));
                }
            }
        });
    });
}


function edit_account() {
    // Change password form area
    $('#change-password').click(function() {
        this.checked === true ? $('#passwords').show() : $('#passwords').hide();
    });

    // Remove picture link
    $('#remove-picture').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Notify the user that the link was clicked
        $('#user-result').html(Theamus.Notify('spinner', 'Removing...'));

        scroll_top(); // Scroll up

        Theamus.Ajax.api({
            type:       'post',
            url:        Theamus.base_url+'/accounts/',
            method:     ['Accounts', 'remove_user_picture'],
            success:    function(data) {
                if (data.error.status === 1) {
                    $('#user-result').html(Theamus.Notify('danger', data.error.message));
                } else if (data.response.data === true) {
                    $('#user-result').html(Theamus.Notify('success', 'Account picture removed.'));

                    // Refresh the page
                    setTimeout(function() { window.location.reload(); }, 2500);
                }
            }
        });
    });

    // Account form submit
    $('#user-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Notify the user that the form was submitted
        $('#user-result').html(Theamus.Notify('spinner', 'Saving...'));

        scroll_top(); // Scroll up

        // Make the call to save the account information
        Theamus.Ajax.api({
            type:       'post',
            url:        Theamus.base_url+'/accounts/',
            method:     ['Accounts', 'save_current_account'],
            data:       { form: this },
            success:    function(data) {
                $('#user-result').show();

                if (data.error.status === 1) {
                    $('#user-result').html(Theamus.Notify('danger', data.error.message));
                } else if (data.response.data === true) {
                    $('#user-result').html(Theamus.Notify('success', 'Account information saved.'));

                    // Hide the results after awhile
                    setTimeout(function() { $('#user-result').html('').hide(); }, 2500);
                }
            }
        });

        return; // Return!
    });
}


function edit_personal() {
    // Personal form submit
    $('#user-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Notify the user that the form was submitted
        $('#user-result').html(Theamus.Notify('spinner', 'Saving...'));

        scroll_top(); // Scroll up

        // Make the call to save the account information
        Theamus.Ajax.api({
            type:       'post',
            url:        Theamus.base_url+'/accounts/',
            method:     ['Accounts', 'save_current_personal'],
            data:       { form: this },
            success:    function(data) {
                $('#user-result').show();

                if (data.error.status === 1) {
                    $('#user-result').html(Theamus.Notify('danger', data.error.message));
                } else if (data.response.data === true) {
                    $('#user-result').html(Theamus.Notify('success', 'Account information saved.'));

                    // Hide the results after awhile
                    setTimeout(function() { $('#user-result').html('').hide(); }, 2500);
                }
            }
        });

        return; // Return!
    });
}


function edit_contact() {
    // Contact form submit
    $('#user-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Notify the user that the form was submitted
        $('#user-result').html(Theamus.Notify('spinner', 'Saving...'));

        scroll_top(); // Scroll up

        // Make the call to save the account information
        Theamus.Ajax.api({
            type:       'post',
            url:        Theamus.base_url+'/accounts/',
            method:     ['Accounts', 'save_current_contact'],
            data:       { form: this },
            success:    function(data) {
                $('#user-result').show();

                if (data.error.status === 1) {
                    $('#user-result').html(Theamus.Notify('danger', data.error.message));
                } else if (data.response.data === true) {
                    $('#user-result').html(Theamus.Notify('success', 'Account information saved.'));

                    // Hide the results after awhile
                    setTimeout(function() { $('#user-result').html('').hide(); }, 2500);
                }
            }
        });

        return false;
    });
}