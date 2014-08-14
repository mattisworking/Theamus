function get_themes(page) {
    var page_number = page === undefined || page === '' ? 1 : page;

    $('#themes-list').html(Theamus.Notify('spinner', 'Loading...'));

    Theamus.Ajax.run({
        url: Theamus.base_url+'/appearance/themes-list&page='+page_number,
        result: 'themes-list',
        type: 'include',
        after: function() { list_listeners(); }
    });

    return false;
}


function change_themes_tab() {
    $('[name="appearance-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-appearance', $(this).attr('data-file'));
        change_admin_window_title('theamus-appearance', $(this).attr('data-title'));
    });
}


function list_listeners() {
    $('[name="edit-theme-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-appearance', 'Edit Theme');
        update_admin_window_content('theamus-appearance', 'appearance/edit?id='+$(this).data('id'));
    });

    $('[name="remove-theme-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-appearance', 'Remove Theme');
        update_admin_window_content('theamus-appearance', 'appearance/remove?id='+$(this).data('id'));
    });

    $("[name='activate-theme-link']").click(function(e) {
        e.preventDefault();

        $('#themes-result').show();

        var notify = null;

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/appearance/',
            method: ['Appearance', 'set_active_theme'],
            data: { custom: { id: $(this).data('id') } },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#themes-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#themes-result').html(Theamus.Notify('success', 'Saved.'));

                    if (notify === null) {
                        notify = setTimeout(function() {
                            $('#themes-result').html('').hide();
                            notify = null;
                        }, 1500);
                    }

                    get_themes();
                }
            }
        });
    });
}


function install() {
    $('#appearance_install-form').submit(function(e) {
        e.preventDefault();

        $('#upload-result').html(Theamus.Notify('spinner', 'Installing...'));
        $('#theamus-appearance').scrollTop(0);

        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/appearance/',
            method: ['Appearance', 'install_theme'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#upload-result').html(Theamus.Notify('danger', data.error.message));
                    $('#appearance_install-form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    $('#upload-result').html(Theamus.Notify('success', 'Theme installed.'));

                    setTimeout(function() {
                        update_admin_window_content('theamus-appearance', 'appearance/');
                        change_admin_window_title('theamus-appearance', 'Theamus Appearance');
                    }, 1500);
                }
            }
        });
    });
}


function edit() {
    $('#appearance_edit-form').submit(function(e) {
        e.preventDefault();

        $('#appearance_edit-result').show();

        $('#appearance_edit-result').html(Theamus.Notify('spinner', 'Updating...'));
        $('#theamus-appearance').scrollTop(0);

        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/appearance/',
            method: ['Appearance', 'install_theme'],
            data: {
                form: this,
                custom: { update: true }
            },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#appearance_edit-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#appearance_edit-result').html(Theamus.Notify('success', 'Theme Updated.'));
                    setTimeout(function() { $('#appearance_edit-result').html('').hide(); }, 1500);
                }

                $('#appearance_edit-form').find('button[type="submit"]').removeAttr('disabled');
            }
        });
    });
}


function remove() {
    $('#appearance_remove-form').submit(function(e) {
        e.preventDefault();

        $('#remove-result').html(Theamus.Notify('spinner', 'Removing...'));
        $('#theamus-appearance').scrollTop(0);

        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/appearance/',
            method: ['Appearance', 'remove_theme'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#remove-result').html(Theamus.Notify('danger', data.error.message));
                    $('#appearance_remove-form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    $('#remove-result').html(Theamus.Notify('success', 'Theme Removed.'));

                    setTimeout(function() {
                        update_admin_window_content('theamus-appearance', 'appearance/');
                        change_admin_window_title('theamus-appearance', 'Theamus Appearance');
                    }, 1500);
                }
            }
        });
    });
}