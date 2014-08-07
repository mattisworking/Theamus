<div id='user-result'></div>

<form class="form-horizontal col-10" id="user-form">
    <h2 class="form-header" style="margin-top: 0;">Contact Information</h2>

    <div class="form-group">
        <label class="control-label col-3" for="email">Email Address</label>
        <div class="col-9">
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $Theamus->User->user['email']; ?>">
            <p class="help-block">Looks something like: "roadrunner@acme.org"</p>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-3" for="phone">Phone Number</label>
        <div class="col-9">
            <input type="text" class="form-control" id="phone" name="phone" maxlength="17" value="<?php echo $Accounts->format_phone($Theamus->User->user['phone']); ?>">
            <p class="help-block">Can be your cell, work, home, fax, or anything else you can think of.<p>
        </div>
    </div>

    <hr class="form-split">

    <div class='form-button-group' style='text-align: right;'>
        <button type="submit" class="btn btn-success">Save Contact Information</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() { edit_contact(); });
</script>