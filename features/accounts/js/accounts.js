function accounts_next_page(page) {
    if (page === undefined || (page % 1) !== 0) {
        page = 1;
    }

    theamus.ajax.api({
        type:       'get',
        url:        theamus.base_url+'accounts/get-user-accounts-list/',
        method:     ['AccountsAPI', 'get_user_accounts_list'],
        data:       {
            custom: { page: page }
        },
        success:    function(data) {
            if (typeof(data) === 'object') {
                $('#accounts-list').html(data.response.data);
            }
        }
    });

    return false;
}

function change_accounts_tab() {
    $('[name="accounts-tab"]').click(function(e) {
        update_admin_window_content('theamus-accounts', $(this).attr('data-file'));
    });
}