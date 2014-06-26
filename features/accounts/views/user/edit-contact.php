<?php

// Define the user information
$user = $tUser->user;

// Define a function that formats phone numbers
function format_phone($number) {
    if ($number != "") {
        $phone = "1 ";
        $phone .= "(".substr($number, 0, 3).")";
        $phone .= " ".substr($number, 3, 3);
        $phone .= "-".substr($number, 6, 10);
    } else {
        $phone = "";
    }

    return $phone;
}

?>

<!-- User Form Result -->
<div id='user-result'></div>

<!-- User Edit Form -->
<form class="form-horizontal col-10" id="user-form">
    <h2 class="form-header" style="margin-top: 0;">Contact Information</h2>

    <!-- Email -->
    <div class="form-group">
        <label class="control-label col-3" for="email">Email Address</label>
        <div class="col-9">
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>">
            <p class="help-block">Looks something like: "roadrunner@acme.org"</p>
        </div>
    </div>

    <!-- Phone Number -->
    <div class="form-group">
        <label class="control-label col-3" for="phone">Phone Number</label>
        <div class="col-9">
            <input type="text" class="form-control" id="phone" name="phone" maxlength="17" value="<?php echo format_phone($user['phone']); ?>">
            <p class="help-block">Can be your cell, work, home, fax, or anything else you can think of.<p>
        </div>
    </div>

    <hr class="form-split">

    <div class='form-button-group' style='text-align: right;'>
        <button type="submit" class="btn btn-success">Save Contact Information</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('#user-form').submit(function(e) {
            e.preventDefault();

            scroll_top();
            theamus.ajax.api({
                type:       'post',
                url:        theamus.base_url+'accounts/user/save-contact/',
                method:     ['Accounts', 'save_current_contact'],
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
                        window.location.reload();
                    }, 2500);
                }
            });

            return false;
        });
    });
</script>