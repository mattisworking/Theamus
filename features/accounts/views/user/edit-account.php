<div id='user-result'></div>

<form class='form-horizontal col-10' id='user-form'>
    <h2 class='form-header' style='margin-top: 0px;'>Login Information</h2>

    <div class='form-group'>
        <label class='control-label col-2'>Username</label>
        <div class=' col-10'>
            <p class='form-control-static'><i><?php echo $Theamus->User->user['username']; ?></i></p>
            <p class='help-block'>This is the username you log in with, it's unique to you and cannot be changed.</p>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-group' style='margin-left: 20px;'>
        <label class='checkbox'>
            <input type='checkbox' name='change_password' id='change-password'>
            Change Password
        </label>
    </div>

    <div id='passwords' style='display:none;'>
        <div class='form-group'>
            <label class='control-label col-3' for='password'>New Password</label>
            <div class='col-9'>
                <input type='password' id='password' name='password' class='form-control'>
            </div>
        </div>

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
        <div class='col-4'>
            <div class='form-group'>
                <label class='control-label'>Current Picture</label>
                <div class='form-control-static' style='text-align: center;'>
                    <?php if ($Theamus->User->user['picture'] == ''): ?>
                    <img src='media/profiles/default-user-picture.png' alt='' height='150'>
                    <?php else: ?>
                    <img src='media/profiles/<?php echo $Theamus->User->user['picture']; ?>' alt='' height='150'>
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

                <?php if ($Theamus->User->user['picture'] != 'default-user-picture.png'): ?>
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
    document.addEventListener('DOMContentLoaded', function() { edit_account(); });
</script>