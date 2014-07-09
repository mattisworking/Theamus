function load_groups() {
    $("#groups_list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url: "groups/groups-list/",
        result: "groups_list",
        type: "include",
        after: function() {
            list_listeners();
        }
    });

    return false;
}

function search_groups() {
    $("#groups_list").html(alert_notify('spinner', 'Searching...'));
    theamus.ajax.run({
        url: "groups/groups-list/&search=" + $("#search").val(),
        result: "groups_list",
        type: "include",
        after: function() {
            list_listeners();
        }
    });
    return false;
}

function list_listeners() {
    $('[name="edit-group-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-groups', 'Edit Group');
        update_admin_window_content('theamus-groups', 'groups/edit?id='+$(this).data('id'));
    });

    $('[name="remove-group-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-groups', 'Edit Group');
        update_admin_window_content('theamus-groups', 'groups/remove-group?id='+$(this).data('id'));
    });
}

function groups_next_page(page) {
    $("#groups_list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url:    "groups/groups-list&search="+ $("#search").val()+"&page="+page,
        result: "groups_list",
        type:   "include"
    });
    return false;
}

function change_groups_tab() {
    $('[name="groups-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-groups', $(this).attr('data-file'));
        change_admin_window_title('theamus-groups', $(this).attr('data-title'));
    });
}

$(document).ready(function() {
    load_groups();
});