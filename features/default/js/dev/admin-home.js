function save_home_listen() {
    // Listen to the save form being submitted
    $('#save-home-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        var apps = $('[name="homeapp"]'), // Define the apps checkboxes
            homeapps = []; // Define the homeapps array

        // Loop through all of the checkboxes, adding their information to the homeapps array
        for (var i = 0; i < apps.length; i++) homeapps.push(apps[i].id+'='+(apps[i].checked ? 1 : 0));

        // Make the call to save the information in the database
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/default/',
            method: ['DefaultAdmin', 'save_home_apps'],
            data: {
                custom: { apps: homeapps.join(',') }
            },
            success: function(data) {
                $('#home-result').show();

                if (data.error.status === 1) {
                    $('#home-result').html(Theamus.Notify('danger', data.error.message));
                } else if(data.response.data !== true) {
                    $('#home-result').html(Theamus.Notify('success', data.response.data));
                }

                setTimeout(function() {$('#home-result').html('').hide();}, 1500);
            }
        });
    });

    return;
}

function update_order(column_id) {
    // Define variables
    var column, i, item, info;

    // Get the column children
    column = $('#' + column_id).children();

    // Loop through all of the children
    for (i = 0; i < column.length; i++) {
        item = column[i];                   // Simplify the column item
        info = item.id.split('=');          // Get the item name
        item.id = info[0] + '=' + (i + 1);  // Reset the item name, with organization
    }

    return false;
}

function get_column_positions(column_id) {
    // Define variables
    var column, i, ids = new Array();

    // Get the column children
    column = $('#' + column_id).children();

    // Loop through all of the children
    for (i = 0; i < column.length; i++) {
        ids.push(column[i].id); // Add this id to the return array
    }

    return ids.join(',');
}

function get_app_positions(column_array) {
    var info = {}; // Initialize the return variable

    // Loop through the columns and add the data to the return array
    for (var i = 0; i < column_array.length; i++) info[column_array[i]] = get_column_positions(column_array[i]);

    return info; // Return the information
}

function save_app_positions() {
    // Update the HTML order of the columns
    update_order('column1');
    update_order('column2');

    // Make the call to save the positions in the database
    Theamus.Ajax.api({
        type: 'post',
        url: Theamus.base_url+'/default/',
        method: ['DefaultAdmin', 'save_app_positions'],
        data: {
            custom: get_app_positions(['column1', 'column2'])
        },
        success: function(data) {
            $('#home-result').show();

            if (data.error.status === 1) {
                $('#home-result').html(Theamus.Notify('danger', data.error.message));
            } else if(data.response.data !== true) {
                $('#home-result').html(Theamus.Notify('success', data.response.data));
            }

            setTimeout(function() {$('#home-result').html('').hide();}, 1500);
        }
    });

    return;
}

function enable_sort() {
    if (typeof $('.col-half').sortable === 'function') {
        $('.col-half').sortable({
            connectWith: '.col-half',
            placeholder: 'sortable-placeholder',
            handle: '.handle',
            stop: function(e, ui) {
                save_app_positions();
            }
        }).disableSelection();
    } else {
        setTimeout(enable_sort, 1000);
    }
}

function update_apps() {
    Theamus.Ajax.api({
        type: 'get',
        url: Theamus.base_url+'/default/',
        method: ['DefaultAdmin', 'update_apps'],
        success: function(data) {
            if (data.error.status === 1) {
                $('#home-result').html(Theamus.Notify('danger', data.error.message));
            } else if(data.response.data !== true) {
                $('#home-result').html(Theamus.Notify('success', data.response.data));
            }
        }
    });

    return;
}

function change_admin_tab() {
    $('[name="admin-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-dashboard', $(this).attr('data-file'));
        change_admin_window_title('theamus-dashboard', $(this).attr('data-title'));
    });
}