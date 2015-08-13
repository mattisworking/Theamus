function accounts_next_page(page) {
    // Define the page number
    if (page === undefined || (page % 1) !== 0) page = 1;

    // Make the call to get the list of users
    Theamus.Ajax.api({
        type: 'get',
        url: Theamus.base_url+'/accounts/',
        method: ['Accounts', 'get_user_accounts_list'],
        data: {
            custom: { page: page }
        },
        success: function(data) {
            if (data.error.status === 1) {
                $('#accounts-list').html(Theamus.Notify('danger', data.error.message));
            } else {
                $('#accounts-list').html(data.response.data);
                add_account_listeners();
            }
        }
    });

    return; // Return!
}


function add_account_listeners() {
    // Edit account links
    $('[name="edit-account-link"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-accounts', '/accounts/admin/edit-account/'+$(this).attr('data-id'));
        change_admin_window_title('theamus-accounts', 'Edit User Account');
    });

    // Remove account links
    $('[name="remove-account-link"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-accounts', '/accounts/admin/remove-account/'+$(this).attr('data-id'));
        change_admin_window_title('theamus-accounts', 'Remove User Account');
    });
}


function change_accounts_tab() {
    // Change tab links
    $('[name="accounts-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-accounts', $(this).attr('data-file'));
        change_admin_window_title('theamus-accounts', $(this).attr('data-title'));
    });
}


function search_accounts_next_page(page) {
    // Define the page number
    if (page === undefined || (page % 1) !== 0) page = 1;

    // Make the call to search for accounts
    Theamus.Ajax.api({
        type: 'get',
        url: Theamus.base_url+'/accounts/',
        method: ['Accounts', 'search_accounts'],
        data: {
            form: $('.search-form'),
            custom: { page: page }
        },
        success: function(data) {
            if (data.error.status === 1) {
                $('account-search-results').html(Theamus.Notify('danger', data.error.message));
            } else {
                $('#account-search-results').html(data.response.data);
                admin_window_run_on_load('add_account_listeners');
            }
        }
    });

    return false;
}


function search_accounts() {
    // Search form submission
    $('.search-form').submit(function(e) {
        e.preventDefault();

        search_accounts_next_page(1);
    });
}


function create_account() {
    // New user form submission
    $('.new-account-form').submit(function(e) {
        e.preventDefault();

        // Scroll to the top of the window and show a loading notification
        $('#theamus-accounts').scrollTop(0);
        $('#create-account-result').html(Theamus.Notify('spinner', 'Creating...'));

        // Make the call to create a new user
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/accounts/',
            method: ['Accounts', 'create_new_account'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#create-account-result').html(Theamus.Notify('danger', data.error.message));
                } else if (data.response.data === true) {
                    $('#create-account-result').html(Theamus.Notify('success', 'Account created successfully.'));

                    // Go back to the list of users
                    setTimeout(function() {
                        update_admin_window_content('theamus-accounts', '/accounts/admin/');
                        change_admin_window_title('theamus-accounts', 'Theamus Accounts');
                    }, 1500);
                }
            }
        });
    });
}


function edit_account() {
    // Toggle the password change fields
    $('[name="change_password"]').click(function(e) {
        this.checked === true ? $('#password-group').show() : $('#password-group').hide();
    });

    // Save account information form submission
    $('.edit-account-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Scroll to the top of the window and show a loading notification
        $('#theamus-accounts').scrollTop(0);
        $('#edit-account-result').html(alert_notify('spinner', 'Saving...'));

        // Make the call to save this user information
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/accounts/admin/save-account',
            method: ['Accounts', 'save_account_information'],
            data: { form: this },
            success: function(data) {
                console.log(data);
                if (data.error.status === 1) {
                    $('#edit-account-result').html(Theamus.Notify('danger', data.error.message));
                } else if (data.response.data === true) {
                    $('#edit-account-result').html(Theamus.Notify('success', 'Account information saved.'));
                }
            }
        });
    });
}

function remove_account() {
    // Go back to the list of people
    $('[name="cancel"]').click(function(e) {
        update_admin_window_content('theamus-accounts', '/accounts/admin/');
        change_admin_window_title('theamus-accounts', 'Theamus Accounts');
    });

    // Remove account form submission
    $('.remove-account-form').submit(function(e) {
        e.preventDefault();

        // Scroll to the top of the window and show a loading notification
        $('#theamus-accounts').scrollTop(0);
        $('#remove-account-result').html(alert_notify('spinner', 'Removing...'));

        // Make the call to remove the account
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/accounts/',
            method: ['Accounts', 'remove_user_account'],
            data: { custom: { id: $('#id').val() } },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#remove-account-result').html(Theamus.Notify('danger', data.error.message));
                } else if (data.response.data === true) {
                    $('#remove-account-result').html(Theamus.Notify('success', 'Account information saved.'));

                    // Go back to the list of users
                    setTimeout(function() {
                        update_admin_window_content('theamus-accounts', '/accounts/admin/');
                        change_admin_window_title('theamus-accounts', 'Theamus Accounts');
                    }, 1500);
                }
            }
        });
    });
}