$(document).ready(function() {
    // Define the children of the page header information
    var toAdd = $('#addToHeader').children();

    // loop through all of the children
    for (i = 0; i < toAdd.length; i++) {
        // If we are looking to add a script element
        if (toAdd[i].name == 'js') {
            // Add the script element!
            $('head').append('<script src="' + toAdd[i].value + '"</script>');
        }

        // If we are looking to add a style element
        if (toAdd[i].name == 'css') {
            // Add the style element!
            $('head').append('<link rel="stylesheet" type="text/css"' + 'href="'
                             + toAdd[i].value + '" />');
        }
    }
    
    // If the home page requests a login
    if ($('#gologin').length > 0) {
        // Redirect to the login page
        window.location.replace($('base')[0].href + 'accounts/login/');
    }
    
    // If the home page requests a custom url
    if ($('#customloc').length > 0) {
        // Go to the custom URL
        window.location.replace($('base')[0].href + $('#customloc')[0].value);
    }
});
