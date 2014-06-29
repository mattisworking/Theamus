function accounts_next_page(page) {
    if (page === undefined || (page % 1) !== 0) {
        page = 1;
    }

    theamus.ajax.api({
        type:       'get',
        url:        theamus.base_url+'accounts/get-user-accounts-list/',
        method:     ['Accounts', 'get_user_accounts_list'],
        data:       {
            custom: { page: page }
        },
        success:    function(data) {
            console.log(data);
            if (typeof(data) === 'object') {
                $('#accounts-list').html(data.response.data);
            }
        }
    });

    return false;
}

function add_account_listeners() {
    $('[name="edit-account-link"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-accounts', 'accounts/admin/edit-account/'+$(this).attr('data-id'));
        change_admin_window_title('theamus-accounts', 'Edit User Account');
    });

    $('[name="remove-account-link"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-accounts', 'accounts/admin/remove-account/'+$(this).attr('data-id'));
        change_admin_window_title('theamus-accounts', 'Remove User Account');
    });
}

function change_accounts_tab() {
    $('[name="accounts-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-accounts', $(this).attr('data-file'));
        change_admin_window_title('theamus-accounts', $(this).attr('data-title'));
    });
}

function encode(str) {
    var encoded = "";
    for (i=0; i<str.length;i++) {
        var a = str.charCodeAt(i),
            b = a ^ 123;
        encoded = encoded+String.fromCharCode(b);
    }
    return encoded;
}