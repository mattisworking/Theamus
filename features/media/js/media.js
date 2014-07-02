function load_media_list() {
    $('#media_list').html(working());
    theamus.ajax.run({
        url:    'media/media-list/',
        result: 'media-list',
        type:   'include'
    });
}

function refresh_media_list() {
    var page = $('#current_page').val();
    $('#media_list').html(working());
    theamus.ajax.run({
        url:    'media/media-list/&page=' + page,
        result: 'media-list',
        type:   'include'
    });
}

function close_add_media() {
    $('#add_window').html('');
    $('#add_window').hide();

    files = new Array;

    return false;
}

function remove_media(id) {
    theamus.ajax.run({
        url:    'media/remove-media/&id='+id,
        result: 'media-result',
        after:  {
            do_function: 'refresh_media_list'
        },
        hide_result: 3
    });

    return false;
}

function next_page(page) {
    $('#users_list').html(working());
    theamus.ajax.run({
        url: 'media/media-list/&page=' + page,
        result: 'media-list',
        type: 'include'
    });
    return false;
}

function change_media_tab() {
    $('[name="media-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-media', $(this).attr('data-file'));
        change_admin_window_title('theamus-media', $(this).attr('data-title'));
    });
}