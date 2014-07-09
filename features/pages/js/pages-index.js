function load_pages() {
    $("#pages_list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url: "pages/pages-list/",
        result: "pages_list",
        type: "include",
        after: function() {
            list_listeners();
        }
    });
    return false;
}

function search_pages() {
    $("#pages_list").html(alert_notify('spinner', 'Searching...'));
    theamus.ajax.run({
        url: "pages/pages-list/&search=" + $("#search").val(),
        result: "pages_list",
        type: "include",
        after: function() {
            list_listeners();
        }
    });
    return false;
}


function list_listeners() {
    $('[name="edit-page-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-pages', 'Edit Group');
        update_admin_window_content('theamus-pages', 'pages/edit?id='+$(this).data('id'));
    });

    $('[name="remove-page-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-pages', 'Edit Group');
        update_admin_window_content('theamus-pages', 'pages/remove-page?id='+$(this).data('id'));
    });
}


// Removes a page
function remove_page(id) {
    admin_scroll_top();
    $("#remove-window").show();
    $("#remove-window").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url:    "pages/remove-page&id=" + id,
        result: "remove-window",
        type:   "include"
    });

	return false;
}

function close_remove_page() {
    $("#remove-window").html("");
    $("#remove-window").hide();

    return false;
}

function submit_remove_page() {
    $("#remove-result").html(alert_notify('spinner', 'Working...'));
    theamus.ajax.run({
        url: "pages/remove/",
        result: "remove-result",
        extra_fields: ["page_id", "remove_links"],
        after: function() {
            $('#remove-result').css('padding-top', '15px');

            $('#theamus-pages').find('button').attr('disabled', 'disabled');

            setTimeout(function() {
                update_admin_window_content('theamus-pages', 'pages/index/');
                change_admin_window_title('theamus-pages', 'Theamus Pages');
            }, 1500);
        }
    });

    return false;
}

function next_page(page) {
    $("#pages_list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url: "pages/pages-list/&search=" + $("#search").val() + "&page=" + page,
        result: "pages_list",
        type: "include",
        after: function() {
            list_listeners();
        }
    });
    return false;
}

function change_pages_tab() {
    $('[name="pages-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-pages', $(this).attr('data-file'));
        change_admin_window_title('theamus-pages', $(this).attr('data-title'));
    });
}

$(document).ready(function() {
    load_pages();
});