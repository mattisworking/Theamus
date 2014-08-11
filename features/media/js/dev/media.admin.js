function get_media_list(page) {
    // Define the page number
    var page_number = page === undefined || (page % 1) !== 0 ? 1 : page;

    // Feedback
    $('#media-list').html(Theamus.Notify('spinner', 'Loading...'));

    // Make the call to get the list
    Theamus.Ajax.run({
        url: Theamus.base_url+'/media/media-list&page='+page_number,
        result: 'media-list',
        type: 'include',
        after: function() {
            remove_media();
            var resize = window.setInterval(function() { resize_admin_window(); }, 10);
            setTimeout(function() { clearInterval(resize); }, 10000);
        }
    });
}


function change_media_tab() {
    $('[name="media-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-media', $(this).attr('data-file'));
        change_admin_window_title('theamus-media', $(this).attr('data-title'));
    });
}

function remove_media() {
    $('.remove').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.

        // Notify the user they clicked on the link and show them
        $('#media-result').html(Theamus.Notify('spinner', 'Removing...'));
        $('#theamus-media').scrollTop(0);

        // Define the media ID
        var media_id = $(this).attr('data-id');

        var response = null; // Initialize the response variable

        // Make the call to delete the image
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/media/',
            method: ['Media', 'remove_media'],
            data: { custom: { id: media_id } },
            success: function(data) {
                $('#media-result').show();

                if (data.error.status === 1) {
                    $('#media-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#media-result').html(Theamus.Notify('success', 'Removed.'));
                    get_media_list(1);

                    if (response !== null) {
                        response = setTimeout(function() { $('#media-result').html('').hide(); }, 1500);
                    }
                }
            }
        });
    });
}