function get_features(page) {
    var page_number = (page === undefined || (page % 1) !== 0) ? 1 : page;

    $('#feature-list').html(Theamus.Notify('spinner', 'Loading...'));

    Theamus.Ajax.run({
        url: Theamus.base_url+'/features/features-list&page='+page_number,
        result: 'feature-list',
        type: 'include',
        after: function() { list_listeners(); }
    });

    return false;
}


function change_features_tab() {
    $('[name="features-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-features', $(this).attr('data-file'));
        change_admin_window_title('theamus-features', $(this).attr('data-title'));
    });
}


function list_listeners() {
    $('[name="edit-feature-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-features', 'Edit Feature');
        update_admin_window_content('theamus-features', 'features/edit?id='+$(this).data('id'));
    });

    $('[name="remove-feature-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-features', 'Remove Feature');
        update_admin_window_content('theamus-features', 'features/remove?id='+$(this).data('id'));
    });
}


function install_feature() {
    $('#feature_install-form').submit(function(e) {
        e.preventDefault();

        $('#feature_install-result').show().html(Theamus.Notify('spinner', 'Installing...'));
        $('#theamus-features').scrollTop(0);

        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/features/',
            method: ['Features', 'install_feature'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#feature_install-result').html(Theamus.Notify('danger', data.error.message));
                    $('#feature_install-form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    $('#feature_install-result').html(Theamus.Notify('success', 'Feature installed.'));

                    setTimeout(function() {
                        change_admin_window_title('theamus-features', 'Theamus Features');
                        update_admin_window_content('theamus-features', 'features/');
                    }, 1500);
                }
            }
        });
    });
}


function edit_feature() {
    $('#feature_edit-form').submit(function(e) {
        e.preventDefault();

        var notify = null;

        $('#feature_edit-result').show().html(Theamus.Notify('spinner', 'Saving...'));
        $('#theamus-features').scrollTop(0);

        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/features/',
            method: ['Features', 'edit_feature'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#feature_edit-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#feature_edit-result').html(Theamus.Notify('success', 'Saved.'));

                    if (notify === null) {
                        notify = setTimeout(function() {
                            $('#feature_edit-result').html('').hide();
                            notify = null;
                        }, 1500);
                    }
                }

                $('#feature_edit-form').find('button[type="submit"]').removeAttr('disabled');
            }
        });
    });
}


function remove_feature() {
    $('#feature_remove-form').submit(function(e) {
        e.preventDefault();

        $('#feature_remove-result').show();

        $('#feature_remove-result').html(Theamus.Notify('spinner', 'Saving...'));
        $('#theamus-features').scrollTop(0);

        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/features/',
            method: ['Features', 'remove_feature'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#feature_remove-result').html(Theamus.Notify('danger', data.error.message));
                    $('#feature_remove-form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    $('#feature_remove-result').html(Theamus.Notify('success', 'Removed.'));

                    setTimeout(function() {
                        change_admin_window_title('theamus-features', 'Theamus Features');
                        update_admin_window_content('theamus-features', 'features/');
                    }, 1500);
                }
            }
        });
    });
}