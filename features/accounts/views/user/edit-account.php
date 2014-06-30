<?php

// Define the user information
$user = $tUser->user;

?>

<!-- User Form Result -->
<div id='user-result'></div>

<!-- User Edit Form -->
<form class='form-horizontal col-10' id='user-form'>
    <h2 class='form-header' style='margin-top: 0px;'>Login Information</h2>

    <!-- Username -->
    <div class='form-group'>
        <label class='control-label col-2'>Username</label>
        <div class=' col-10'>
            <p class='form-control-static'><i><?php echo $user['username']; ?></i></p>
            <p class='help-block'>This is the username you log in with, it's unique to you and cannot be changed.</p>
        </div>
    </div>

    <hr class='form-split'>

    <!-- Change Password -->
    <div class='form-group' style='margin-left: 20px;'>
        <label class='checkbox'>
            <input type='checkbox' name='change_password' id='change-password'>
            Change Password
        </label>
    </div>

    <div id='passwords' style='display:none;'>
        <!-- Password -->
        <div class='form-group'>
            <label class='control-label col-3' for='password'>New Password</label>
            <div class='col-9'>
                <input type='password' id='password' name='password' class='form-control'>
            </div>
        </div>

        <!-- Password Repeat -->
        <div class='form-group'>
            <label class='control-label col-3' for='password-again'>Password Again</label>
            <div class='col-9'>
                <input type='password' name='password_again' id='password-again' class='form-control'>
            </div>
        </div>
    </div>

    <hr class='form-split' id='password-split'>

    <h2 class='form-header'>Profile Picture</h2>

    <div class='col-12'>
        <!-- Profile Picture -->
        <div class='col-4'>
            <div class='form-group'>
                <label class='control-label'>Current Picture</label>
                <div class='form-control-static' style='text-align: center;'>
                    <?php if ($user['picture'] == ''): ?>
                    <img src='media/profiles/default-user-picture.png' alt='' height='150'>
                    <?php else: ?>
                    <img src='media/profiles/<?php echo $user['picture']; ?>' alt='' height='150'>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class='col-8'>
            <div class='form-group'>
                <label class='control-label' for='picture'>Change Picture</label>
                <div class='form-control-static'>
                    <input type='file' class='form-control' id='picture' name='picture'>
                </div>

                <?php if ($tUser->user['picture'] != 'default-user-picture.png'): ?>
                <p class='form-control-static'>
                    <a href='#' id='remove-picture'>Remove Picture</a>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group' style='text-align: right;'>
        <button type='submit' class='btn btn-success'>Save Account Information</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#change-password').click(function() {
            if (this.checked === true) {
                $('#passwords').show();
            } else {
                $('#passwords').hide();
            }
        });

        $('#remove-picture').click(function(e) {
            e.preventDefault();

            scroll_top();
            theamus.ajax.api({
                type:       'post',
                url:        theamus.base_url+'accounts/user/save-account/',
                method:     ['Accounts', 'remove_user_picture'],
                success:    function(data) {
                    if (typeof(data) !== 'object') {
                        $('#user-result').html(alert_notify('danger', 'There was an issue sending this data to the server.'));
                        return;
                    }

                    if (typeof(data) === 'string') {
                        $('#user-result').html(data.response.data);
                        return;
                    }

                    $('#user-result').html(alert_notify('success', 'Profile picture removed.'));

                    setTimeout(function() {
                        window.location.reload();
                    }, 2500);
                }
            });
        });

        $('#user-form').submit(function(e) {
            e.preventDefault();

            scroll_top();
            theamus.ajax.api({
                type:       'post',
                url:        theamus.base_url+'accounts/user/save-account/',
                method:     ['Accounts', 'save_current_account'],
                data:       { form: this },
                success:    function(data) {
                    if (typeof(data) !== 'object') {
                        $('#user-result').html(alert_notify('danger', 'There was an issue sending this data to the server.'));
                        return;
                    }

                    if (typeof(data) === 'string') {
                        $('#user-result').html(data.response.data);
                        return;
                    }

                    $('#user-result').html(alert_notify('success', 'This information has been saved.'));

                    setTimeout(function() {
                        $('#user-result').html('');
                        $('#user-result').hide();
                    }, 2500);
                }
            });

            return false;
        });
    });
</script>