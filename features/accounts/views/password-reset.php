<?php $Theamus->notify("info", " If you refresh this page after the code has been sent, you will need to fill out this information again to receive a new reset code."); ?>

<section type="card" id="reset_code">
    <header type="card_header">Account Information</header>
    
    <hr class="card_section-split">
    
    <form id="send_password_reset-form">
        <div class="card_input-wrapper">
            <input type="text"
                   name="username"
                   data-label="Your Username">
        </div>
        
        <div class="card_input-wrapper">
            <input type="email"
                   name="email"
                   data-label="Your Email Address">
        </div>
        
        <div class="card_button-wrapper">
            <button type="submit" class="success">Send Reset Code</button>
        </div>
    </form>
</section>

<section type="card" id="password_reset">
    <header type="card_header">Reset Your Password</header>
    
    <div class="alert alert-info accounts_reset-from-file">
        <span class="glyphicon ion-information"></span>
        Look in the root folder of your Theamus installation for a file named "password-reset-code.txt"
    </div>
    
    <hr class="card_section-split" id="accounts_reset-from-file-split">
    
    <form id="reset_password">
        <div class="card_input-wrapper">
            <input type="text"
                   name="reset_code"
                   data-label="Reset Code">
        </div>
        
        <hr class="card_section-split">
        
        <div class="card_input-wrapper">
            <input type="password"
                   name="password"
                   data-label="New Password">
        </div>
        
        <div class="card_input-wrapper">
            <input type="password"
                   name="repeat_password"
                   data-label="Repeat New Password">
        </div>
        
        <div class="card_button-wrapper">
            <button type="submit" class="primary">Reset Password</button>
        </div>
    </form>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    send_password_reset();
});
</script>