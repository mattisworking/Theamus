function load_nav() {
    $("#navigation-list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url: "navigation/navigation-list/",
        result: "navigation-list",
        type: "include",
        after: function() {
            list_listeners();
        }
    });
    return false;
}

function search_nav() {
    $("#navigation-list").html(alert_notify('spinner', 'Searching...'));
    theamus.ajax.run({
        url: "navigation/navigation-list/&search=" + $("#search").val(),
        result: "navigation-list",
        type: "include",
        after: function() {
            list_listeners();
        }
    });
    return false;
}

function submit_remove_link() {
    $("#remove-result").html(alert_notify('spinner', 'Removing Link...'));
    $("#remove-link-form").find('button').attr('disabled', 'disabled');
    theamus.ajax.run({
        url: "navigation/remove/",
        result: "remove-result",
        extra_fields: "link_id",
        hide_result: 3,
        after: function() {
            setTimeout(
                function() {
                    change_admin_window_title('theamus-navigation', 'Theamus Navigation');
                    update_admin_window_content('theamus-navigation', 'navigation/');
                }, 1500);
        }
    });

    return false;
}

function next_page(page) {
    $("#navigation-list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url: "navigation/navigation-list&search=" + $("#search").val() + "&page=" + page,
        result: "navigation-list",
        type: "include"
    });
    return false;
}

function change_navigation_tab() {
    $('[name="navigation-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-navigation', $(this).attr('data-file'));
        change_admin_window_title('theamus-navigation', $(this).attr('data-title'));
    });
}

function list_listeners() {
    $('[name="edit-navigation-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-navigation', 'Edit Link');
        update_admin_window_content('theamus-navigation', 'navigation/edit?id='+$(this).data('id'));
    });

    $('[name="remove-navigation-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-navigation', 'Remove Link');
        update_admin_window_content('theamus-navigation', 'navigation/remove-link?id='+$(this).data('id'));
    });
}

$(document).ready(function() {
    load_nav();
});