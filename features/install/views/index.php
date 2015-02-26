<div class='homepage-content-header'>Welcome to the Theamus Installer</div>
<div class='homepage-content-tag'>
    <?php
    $mod_rewrite_enabled = false;
    if (function_exists('apache_get_modules')) {
        if (in_array('mod_rewrite', apache_get_modules())) {
            $mod_rewrite_enabled = true;
        }
    }

    if ($mod_rewrite_enabled == false) {
        alert_notify('info', 'The Apache module <strong>mod_rewrite</strong> must be enabled for Theamus to work.');
    } else {
        echo '<button type="button" id="next-step" class="btn btn-primary">Get Started <span class="glyphicon ion-arrow-right-c"></span></button>';
    }
    ?>
</div>

<script type='text/javascript'>
    $(function() {
        $('#next-step').click(function() {
            window.location = Theamus.base_url+'/install/dependencies/';
        });
    });
</script>