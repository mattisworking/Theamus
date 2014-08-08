function change_groups_tab() {
    $('[name="groups-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-groups', $(this).attr('data-file'));
        change_admin_window_title('theamus-groups', $(this).attr('data-title'));
    });
    
    return; // Return!
}


function define_homepage() {
    if ($('#type').val() === 'nooverride') var ret = 'false';
    else {
        var type = $("#type").val(),
            elements = $("#"+type+" :input"),
            ret = "{t:homepage;type=\""+type+"\";";

        if (type === "page") ret += "id=\""+elements[0].value+"\";";
        if (type === "custom") ret += "url=\""+elements[0].value.replace(/\./g, "{p}").replace(/\-/g, "{d}").replace(/\//g, "{fs}")+"\";";
        if (type === "feature") {
            ret += "id=\""+elements[0].value+"\";";
            ret += "file=\""+elements[1].value+"\";";
        }

        ret += ":}";
    }

    return ret;
}


function load_groups_list() {
    // Notify the user that the groups list is being loaded
    $('#groups-list').html(Theamus.Notify('spinner', 'Loading...'));
    
    // Load the groups list
    Theamus.Ajax.run({
        url:    'groups/groups-list/',
        result: 'groups-list',
        type:   'include',
        after: function() {
            list_listeners();
        }
    });

    return; // Return!
}


function groups_next_page(page) {
    // Notify the user that the groups list is being loaded
    $('#groups-list').html(Theamus.Notify('spinner', 'Loading...'));
    
    // Define the search value
    var search = $('#search').val() === undefined ? '' : $('#search').val();
    
    // Search for groups
    Theamus.Ajax.run({
        url:    'groups/groups-list&search='+search+'&page='+page,
        result: 'groups-list',
        type:   'include',
        after: function() {
            list_listeners();
        }
    });
    
    return false; // Return false to go nowhere!
}


function list_listeners() {
    // Listen to the edit links
    $('[name="edit-group-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-groups', 'Edit Group');
        update_admin_window_content('theamus-groups', 'groups/edit?id='+$(this).data('id'));
    });

    // Listen to the remove links
    $('[name="remove-group-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-groups', 'Edit Group');
        update_admin_window_content('theamus-groups', 'groups/remove?id='+$(this).data('id'));
    });
    
    return; // Return!
}


function search_groups() {
    // Search for groups when the user wants to
    $('#search-groups-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing
        
        groups_next_page(1); // Search already built in!
    });
    
    return; // Return!
}


function create_group() {
    // Create a new group form submitted
    $('#create-group-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing
        
        // Notify the user that the group is being created (scroll to top to see)
        $('#group-result').html(Theamus.Notify('spinner', 'Creating...'));
        $('#theamus-groups').scrollTop(0);
        
        // Disable the submit button (to avoid multiple clicks)
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');
        
        // Make the call to create the group
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/groups/',
            method: ['Groups', 'create_group'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#create-group-form').find('button[type="submit"]').removeAttr('disabled');
                    $('#group-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#group-result').html(Theamus.Notify('success', 'Group created successfully.'));
                    
                    setTimeout(function() {
                        change_admin_window_title('theamus-groups', 'Theamus Groups');
                        update_admin_window_content('theamus-groups', 'groups/');
                    }, 2500);
                }
            }
        });
    });
    
    return; // Return!
}


function edit_group() {
    // Change homepage type link
    $('[name="type-link"]').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.
        
        var previous = $('#type').val(); // Define the type that WAS open before
        
        // Update the hidden type to show that this one is open
        $('#type').val($(this).attr('data-type'));

        $('#'+previous).hide(); // Hide the one that WAS open
        $('#'+$(this).attr('data-type')).show();  // Show the desired one
        
        resize_admin_window(); // Resize the admin window to fit
    });
    
    // Feature folder update files
    $('#featurename').change(function() {
        // Make the call to get the feature files
        Theamus.Ajax.api({
            type: 'get',
            url: Theamus.base_url+'/groups/',
            method: ['Groups', 'get_feature_file_options'],
            data: { custom: { feature_folder: $(this).find(':selected').attr('data-alias') } },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#feature-file-list').html('<option>Failed to get feature files.</option>');
                } else {
                    $('#feature-file-list').html(data.response.data);
                }
            }
        });
    });
    
    // Form submission
    $('#save-group-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.
        
        // Notify the user the form has been submitted and show them
        $('#group-result').show().html(Theamus.Notify('spinner', 'Saving...'));
        $('#theamus-groups').scrollTop(0);
        
        // Disable the save button to avoid multi clicks
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');
        
        // Make the call to save this information
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/groups/',
            method: ['Groups', 'save_group'],
            data: { 
                form: this,
                custom: { homepage: define_homepage() }
            },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#group-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#group-result').html(Theamus.Notify('success', 'Group saved.'));
                    
                    setTimeout(function() { $('#group-result').html('').hide(); }, 2500);
                }
                
                $('#save-group-form').find('button[type="submit"]').removeAttr('disabled');
            }
        });
    });
    
    return; // Return!
}


function remove_group() {
    // Form submission
    $('#remove-group-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.
        
        // Notify the user the form has been submitted and show them
        $('#remove-result').show().html(Theamus.Notify('spinner', 'Removing...'));
        $('#theamus-groups').scrollTop(0);
        
        // Disable the save button to avoid multi clicks
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');
        
        // Make the call to save this information
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/groups/',
            method: ['Groups', 'remove_group'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#remove-result').html(Theamus.Notify('danger', data.error.message));
                    $('#remove-group-form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    $('#remove-result').html(Theamus.Notify('success', 'Group removed.'));
                    
                    setTimeout(function() {
                        change_admin_window_title('theamus-groups', 'Theamus Groups');
                        update_admin_window_content('theamus-groups', 'groups/');
                    }, 2000);
                }
            }
        });
    });
    
    return; // Return!
}