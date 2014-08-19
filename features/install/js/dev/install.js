function generate_salt() {
    var return_salt = "",
        possible_characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for (var i=0; i < 25; i++) {
        return_salt += possible_characters.charAt(Math.floor(Math.random() * possible_characters.length));
    }

    return return_salt;
}


function check_form() {
    return Theamus.Ajax.api({
        type: 'post',
        url: Theamus.base_url+'/install/',
        method: ['Install', 'check_form'],
        data: { form: $('#install_setup-form') },
        success: function(data) {
            if (data.error.status === 1) {
                $('#install_setup-form').find('button[type="submit"]').removeAttr('disabled');
                $('#install_setup-result').html(Theamus.Notify('danger', data.error.message));
                return false;
            }

            $('#install_setup-result').html(Theamus.Notify('spinner', 'Installing the database structure and data...'));
            return true;
        }
    });
}


function install_database_config() {
    return Theamus.Ajax.api({
        type: 'post',
        url: Theamus.base_url+'/install/',
        method: ['Install', 'install_database_config'],
        data: { form: $('#install_setup-form') },
        success: function(data) {
            if (data.error.status === 1) {
                $('#install_setup-form').find('button[type="submit"]').removeAttr('disabled');
                $('#install_setup-result').html(Theamus.Notify('danger', data.error.message));
                return false;
            }

            $('#install_setup-result').html(Theamus.Notify('spinner', 'Creating the first user...'));
            return true;
        }
    });
}


function install_user() {
    return Theamus.Ajax.api({
        type: 'post',
        url: Theamus.base_url+'/install/',
        method: ['Install', 'create_first_user'],
        data: { form: $('#install_setup-form') },
        success: function(data) {
            if (data.error.status === 1) {
                $('#install_setup-form').find('button[type="submit"]').removeAttr('disabled');
                $('#install_setup-result').html(Theamus.Notify('danger', data.error.message));
                return false;
            }

            $('#install_setup-result').html(Theamus.Notify('spinner', 'Finishing the installation...'));
            return true;
        }
    });
}


function finish_installation() {
    return Theamus.Ajax.api({
        type: 'post',
        url: Theamus.base_url+'/install/',
        method: ['Install', 'finish_installation'],
        data: { form: $('#install_setup-form') },
        success: function(data) {
            if (data.error.status === 1) {
                $('#install_setup-form').find('button[type="submit"]').removeAttr('disabled');
                $('#install_setup-result').html(Theamus.Notify('danger', data.error.message));
                return false;
            }

            $('#install_setup-result').html(Theamus.Notify('success', 'Theamus was installed successfully!'));
            setTimeout(function() {
                window.location = Theamus.base_url;
            }, 1500);
        }
    });
}


function install_setup() {
    $('#show-advanced-options').click(function() {
        if (this.checked) $('#advanced-options').show();
        else $('#advanced-options').hide();
    });

    $('#generate-password-salt').click(function() {
        $('#security-password-salt').val(generate_salt());
    });

    $('#generate-session-salt').click(function() {
        $('#security-session-salt').val(generate_salt());
    });

    $('#install_setup-form').submit(function(e) {
        e.preventDefault();

        $('#install_setup-result').html(Theamus.Notify('spinner', 'Checking the database connection and other installation setup values...'));

        $('#install_setup-form').find('button[type="submit"]').attr('disabled', 'disabled');

        $(window).scrollTop(0);
        Theamus.Ajax.iterate_calls(['check_form', 'install_database_config', 'install_user', 'finish_installation'], 1);
    });
}