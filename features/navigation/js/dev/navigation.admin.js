function change_navigation_tab() {
    $('[name="navigation-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-navigation', $(this).attr('data-file'));
        change_admin_window_title('theamus-navigation', $(this).attr('data-title'));
    });
}


function list_listeners() {
    // Edit link
    $('[name="edit-navigation-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-navigation', 'Edit Link');
        update_admin_window_content('theamus-navigation', '/navigation/edit?id='+$(this).data('id'));
    });

    // Remove link
    $('[name="remove-navigation-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-navigation', 'Remove Link');
        update_admin_window_content('theamus-navigation', '/navigation/remove?id='+$(this).data('id'));
    });
}


function change_path(to) {
    // Hide the old element
    $('#'+$('#path-type').val()+'-wrapper').hide();

    // Update the type element
    $('#path-type').val(to);

    // Show the new element
    $('#'+to+'-wrapper').show();

    resize_admin_window();
}


function load_pages_select() {
    // Make the call to get the pages options
    Theamus.Ajax.api({
        type: 'get',
        url: Theamus.base_url+'/navigation/',
        method: ['Navigation', 'get_pages_select'],
        data: { custom: { page: $('#page').length > 0 ? $('#page').val() : '' } },
        success: function(data) {
            if (data.error.status === 1) {
                $('#page-select').html('<option>'+data.error.message+'</option>');
            } else {
                $('#page-select').html(data.response.data);
            }
        }
    });
}


function load_features_select() {
    // Make the call to get the pages options
    Theamus.Ajax.api({
        type: 'get',
        url: Theamus.base_url+'/navigation/',
        method: ['Navigation', 'get_features_select'],
        data: { custom: { feature: $('#feature').length > 0 ? $('#feature').val() : '' } },
        success: function(data) {
            if (data.error.status === 1) {
                $('#feature-select').html('<option>'+data.error.message+'</option>');
            } else {
                $('#feature-select').html(data.response.data);
            }
        }
    });
}


function load_feature_files_select() {
    // Make the call to get the pages options
    Theamus.Ajax.api({
        type: 'get',
        url: Theamus.base_url+'/navigation/',
        method: ['Navigation', 'get_feature_files_select'],
        data: { custom: {
                feature: $('#feature-select').val(),
                file:  $('#feature-file').length > 0 ? $('#feature-file').val() : ''
            } },
        success: function(data) {
            if (data.error.status === 1) {
                $('#feature-file-select').html('<option>'+data.error.message+'</option>');
            } else {
                $('#feature-file-select').html(data.response.data);
            }
        }
    });
}


function load_groups_select() {
    // Make the call to get the pages options
    Theamus.Ajax.api({
        type: 'get',
        url: Theamus.base_url+'/navigation/',
        method: ['Navigation', 'get_groups_select'],
        data: { custom: { groups: $('#groups').length > 0 ? $('#groups').val() : '' } },
        success: function(data) {
            if (data.error.status === 1) {
                $('#group-select').html('<option>'+data.error.message+'</option>');
            } else {
                $('#group-select').html(data.response.data);
            }
        }
    });
}


function get_navigation_links(page) {
    // Define the page number for the search results
    var page_number = page === undefined || (page % 1) !== 0 ? 1 : page;

    // Define the search query
    var search_query = $('#navigation-search').length === 0 ? '' : $('#navigation-search').val();

    // Make the call to get the list of links
    Theamus.Ajax.run({
        url: Theamus.base_url+'/navigation/links-list&search='+search_query+'&page='+page_number,
        result: 'navigation-list',
        type: 'include',
        after: function() {
            list_listeners();
        }
    });

    return false; // Go nowhere, do nothing
}


function search_links() {
    $('#navigation-search-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing
        get_navigation_links(1);
    });
}


function create_link() {
    // Handle the changing of path types
    $('[name="path"]').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.
        change_path(this.id);
    });

    $('#feature-select').change(function() {
        load_feature_files_select();
    });

    $('#create-link-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.

        // Let the user know the form was submitted and show them
        $('#navigation-result').html(Theamus.Notify('spinner', 'Creating...'));
        $('#theamus-navigation').scrollTop(0);

        // Disable the submit button
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        // Make the call to get the pages options
        Theamus.Ajax.api({
            type: 'get',
            url: Theamus.base_url+'/navigation/',
            method: ['Navigation', 'create_link'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#navigation-result').html(Theamus.Notify('danger', data.error.message));
                    $('#create-link-form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    $('#navigation-result').html(Theamus.Notify('success', 'Link created successfully.'));

                    setTimeout(function() {
                        change_admin_window_title('theamus-navigation', 'Theamus Navigation');
                        update_admin_window_content('theamus-navigation', '/navigation/');
                    }, 1500);
                }
            }
        });
    });
}


function edit_link() {
    // Handle the changing of path types
    $('[name="path"]').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.
        change_path(this.id);
    });

    $('#feature-select').change(function() {
        load_feature_files_select();
    });

    $('#edit-link-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.

        // Let the user know the form was submitted and show them
        $('#navigation-result').html(Theamus.Notify('spinner', 'Saving...'));
        $('#theamus-navigation').scrollTop(0);

        // Disable the submit button
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        // Make the call to get the pages options
        Theamus.Ajax.api({
            type: 'get',
            url: Theamus.base_url+'/navigation/',
            method: ['Navigation', 'save_link'],
            data: { form: this },
            success: function(data) {
                $('#navigation-result').show();

                if (data.error.status === 1) {
                    $('#navigation-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#navigation-result').html(Theamus.Notify('success', 'Saved.'));
                    setTimeout(function() { $('#navigation-result').html('').hide(); }, 1500);
                }

                $('#edit-link-form').find('button[type="submit"]').removeAttr('disabled');
            }
        });
    });
}


function remove_link() {
    $('#remove-link-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.

        // Let the user know the form was submitted and show them
        $('#remove-result').html(Theamus.Notify('spinner', 'Removing...'));
        $('#theamus-navigation').scrollTop(0);

        // Disable the submit button
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        // Make the call to get the pages options
        Theamus.Ajax.api({
            type: 'get',
            url: Theamus.base_url+'/navigation/',
            method: ['Navigation', 'remove_link'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#remove-result').html(Theamus.Notify('danger', data.error.message));
                    $('#remove-link-form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    $('#remove-result').html(Theamus.Notify('success', 'Removed.'));

                    setTimeout(function() {
                        change_admin_window_title('theamus-navigation', 'Theamus Navigation');
                        update_admin_window_content('theamus-navigation', '/navigation/');
                    }, 1500);
                }
            }
        });
    });
}