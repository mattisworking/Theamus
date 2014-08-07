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

<script>
    document.addEventListener('DOMContentLoaded', function() { register(); });
</script>