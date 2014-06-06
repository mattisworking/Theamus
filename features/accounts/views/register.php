<div id='register-result'></div>
<form class='form col-4' id='register-form'>
    <div class='form-group has-feedback' id='username-group'>
        <label class='control-label' for='username'>Username</label>
        <input type='text' id='username' name='username' class='form-control' placeholder='Enter your username...'>
        <p class='help-block'>The username you choose is permanent. It cannot be changed.</p>
        <span class='glyphicon form-control-feedback'></span>
    </div>

    <div class='form-group has-feedback' id='password-group'>
        <label class='control-label' for='password'>Password</label>
        <input type='password' id='password' name='password' class='form-control has-success'>
        <span class='glyphicon form-control-feedback'></span>
    </div>

    <div class='form-group has-feedback' id='password-repeat-group'>
        <label class='control-label' for='password-again'>Repeat Password</label>
        <input type='password' id='password-again' name='password_again' class='form-control'>
        <span class='glyphicon form-control-feedback'></span>
    </div>

    <hr class='form-split'>

    <div class='form-group has-feedback' id='email-group'>
        <label class='control-label' for='email'>Email Address</label>
        <input type='email' id='email' name='email' class='form-control' placeholder='user@example.com'>
        <span class='glyphicon form-control-feedback'></span>
    </div>

    <hr class='form-split'>

    <div class='form-group'>
        <label class='control-label' for='firstname'>First name</label>
        <input type='text' id='first-name' name='firstname' class='form-control' placeholder='Enter your first name...'>
    </div>

    <div class='form-group'>
        <label class='control-label' for='lastname'>Last name</label>
        <input type='text' id='last-name' name='lastname' class='form-control' placeholder='Enter your last name...'>
    </div>

    <hr class='form-split'>

    <div class='site-formsubmitrow'>
        <button type='submit' id='register-btn' class='btn btn-success'>Register</button>
        or <a href='accounts/login/'>Login</a>
    </div>
</form>

<script type='text/javascript'>
function change_glyph(e, t, n) {
    $('#' + e + ' .glyphicon').removeClass('ion-checkmark');
    $('#' + e + ' .glyphicon').removeClass('ion-close');
    $('#' + e + ' .glyphicon').attr('title', n);
    if (t !== false) {
        $('#' + e + ' .glyphicon').addClass(t)
    }
}
$('#username').keyup(function () {
    if ($(this).val() === '') {
        change_glyph('username-group', false, '')
    } else {
        theamus.ajax.api({
            type: 'get',
            method: ['AccountsAPI', 'check_username'],
            url: 'accounts/check_username',
            data: {
                custom: {
                    username: $(this).val()
                }
            },
            success: function (e) {
                if (typeof e !== 'object' || e.error.status === 1) {
                    change_glyph('username-group', 'ion-close', 'There was an error checking the username.')
                } else {
                    if (e.response.data === 'invalid') {
                        change_glyph('username-group', 'ion-close', 'This username is invalid.')
                    } else if (e.response.data === 'taken') {
                        change_glyph('username-group', 'ion-close', 'This username has already been taken.')
                    } else {
                        change_glyph('username-group', 'ion-checkmark', '')
                    }
                }
            }
        })
    }
});
$('#password').keyup(function () {
    if ($(this).val() === '') {
        change_glyph('password-group', false, '')
    } else {
        theamus.ajax.api({
            type: 'get',
            method: ['AccountsAPI', 'check_password'],
            url: 'accounts/check_password',
            data: {
                custom: {
                    password: $(this).val()
                }
            },
            success: function (e) {
                if (typeof e !== 'object' || e.error.status === 1) {
                    change_glyph('password-group', 'ion-close', 'There was an error checking the password.')
                } else {
                    if (e.response.data === 'short') {
                        change_glyph('password-group', 'ion-close', 'The password is too short!')
                    } else {
                        change_glyph('password-group', 'ion-checkmark', '')
                    }
                }
            }
        })
    }
});
$('#password-repeat').keyup(function () {
    if ($(this).val() === '') {
        change_glyph('password-repeat-group', false, '')
    } else if ($(this).val() !== $('#password').val()) {
        change_glyph('password-repeat-group', 'ion-close', 'The passwords do not match')
    } else if ($(this).val() === $('#password').val()) {
        change_glyph('password-repeat-group', 'ion-checkmark', '')
    }
});
$('#email').keyup(function () {
    if ($(this).val() === '') {
        change_glyph('email-group', false, '')
    } else {
        theamus.ajax.api({
            type: 'get',
            method: ['AccountsAPI', 'check_email'],
            url: 'accounts/check_email',
            data: {
                custom: {
                    email: $(this).val()
                }
            },
            success: function (e) {
                if (typeof e !== 'object' || e.error.status === 1) {
                    change_glyph('email-group', 'ion-close', 'There was an error checking the email.')
                } else {
                    if (e.response.data === 'invalid') {
                        change_glyph('email-group', 'ion-close', 'This email address is invalid.')
                    } else {
                        change_glyph('email-group', 'ion-checkmark', '')
                    }
                }
            }
        })
    }
});
$('#register-form').submit(function (e) {
    e.preventDefault();
    $('#register-result').html(alert_notify('spinner', 'Registering...'));

    setTimeout(function() {
        theamus.ajax.api({
            type: 'post',
            method: ['AccountsAPI', 'register_user'],
            url: 'accounts/register_user',
            data: {
                form: $('#register-form')
            },
            success: function (e) {
                var t = $('#register-btn');
                t.attr('disabled', true);
                if (typeof e !== 'object' || e.error.status === 1) {
                    $('#register-result').html(alert_notify('danger', 'There was an error submitting the registration.'));
                    t.attr('disabled', false)
                } else {
                    if (typeof(e.response.data) !== 'boolean') {
                        t.attr('disabled', false);
                        $('#register-result').html(e.response.data);
                        return;
                    }

                    $('#register-result').html(alert_notify('success', 'The account registration was successfull.  Check your email for an activation code!'));
                }
            }
        })
    }, 1000);
})
</script>