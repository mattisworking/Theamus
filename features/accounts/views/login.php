<?php $redirect = isset($_GET['redirect']) ? filter_input(INPUT_GET, "redirect") : base_url; ?>

<div id="login-result"></div>

<form class="form" id="login-form">
    <!-- Redirect URL -->
    <input type="hidden" id="redirect_url" value="<?php echo $redirect; ?>" />

    <!-- Username -->
    <div class="form-group">
        <label class="control-label" for="username">Username</label>
        <input type="text" id="username" name="username" class="form-control">
    </div>

    <!-- Password -->
    <div class="form-group">
        <label class="control-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control">
    </div>

    <hr class="form-split">

    <!-- Session Preserve -->
    <div class="form-group">
        <label>
            Stay logged in
            <input type="checkbox" name="keep_session" checked>
        </label>
    </div>

	<div class="form-button-group">
        <button type="submit" class="form-control btn btn-primary">Login</button>
	</div>
</form>

<script type="text/javascript">
    $(function(){$("#login-form").submit(function(e){e.preventDefault();var t=$("#login-result");theamus.ajax.api({type:"get",url:"accounts/user-login/",method:["AccountsApi","login"],data:{form:$("#login-form")},success:function(e){console.log(e);if(typeof e!=="object"){t.html(alert_notify("danger","There was an issue logging in."))}else{if(e.error.status===1){t.html(alert_notify("danger",e.error.message))}else{var n=e.response.data;if(n===true){window.location=$("#redirect_url").val()}else{t.html(alert_notify("danger",n.error.message))}}}}})})})
</script>