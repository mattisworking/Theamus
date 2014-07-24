function saveCustom() {
    var homePage = getHomePage();
    theamus.ajax.run({
        url: "settings/saveCustom/&home=" + homePage,
        result: "custom-result",
        extra_fields: 'site-name'
    });
    return false;
}

function getHomePage() {
    var home = '';

    if ($('#setting-session')[0].value === 'true') {
        home = $('#out')[0].value + ':!' + $('#in')[0].value;
    } else if ($('#reqlogin')[0].value === 'true') {
        if ($('#type')[0].value === 'login') {
            home = 'require-login:;1';
        } else if ($('#type')[0].value === 'page') {
            home = 'require-login';
            elements = $('#' + $('#type')[0].value + ' :input');
            for (var i = 0; i < elements.length; i++) {
                home += ':;' + elements[i].value;
            }
        } else {
            home = 'require-login:' + $('#type')[0].value;
            elements = $('#' + $('#type')[0].value + ' :input');
            for (var i = 0; i < elements.length; i++) {
                home += ':;' + elements[i].value;
            }
        }
    } else {
        home = $('#type')[0].value;
        elements = $('#' + $('#type')[0].value + ' :input');
        for (var i = 0; i < elements.length; i++) {
            home += ':;' + elements[i].value;
        }
    }

    return home;
}

function saveSettings() {
    admin_scroll_top();
    theamus.ajax.run({
        url: "settings/saveSettings/",
        result: "custom-result",
        form: "custom-form"
    });
    return false;
}


/**
 * Toggles the email configuration area
 */
function showEmailConfig() {
	if ($('#config-email')[0].checked) {
        $('#email-container').show();
    } else {
        $('#email-container').hide();
    }
}