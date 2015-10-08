<?php

$redirect = isset($_GET['redirect']) ? filter_input(INPUT_GET, 'redirect') : $Theamus->Call->base_url;
$show_password_reset = false;
if (count($Theamus->Call->parameters) > 0) {
    if ($Theamus->Call->parameters[0] == "reset") $show_password_reset = true;
}

?>

<div id='login-result'></div>

<form class='form' id='login-form'>
    <input type='hidden' id='redirect_url' value='<?php echo $redirect; ?>' />
    
    <?php if ($show_password_reset) echo $Theamus->notify("info", "Your password has been reset."); ?>

    <div class='form-group'>
        <label class='control-label' for='username'>Username</label>
        <input type='text' id='username' name='username' class='form-control'>
    </div>

    <div class='form-group'>
        <label class='control-label' for='password'>Password</label>
        <input type='password' id='password' name='password' class='form-control'>
    </div>

    <hr class='form-split'>

    <div class='form-group'>
        <label>
            Stay logged in
            <input type='checkbox' name='keep_session' checked>
        </label>
    </div>

	<div class='form-button-group'>
        <button type='submit' class='form-control btn btn-primary'>Login</button>
	</div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() { login(); });
</script>