function load_nav() {
    $("#navigation-list").html(working());
    theamus.ajax.run({
        url: "navigation/navigation-list/",
        result: "navigation-list",
        type: "include"
    });
    return false;
}

function search_nav() {
    $("#navigation-list").html(working());
    theamus.ajax.run({
        url: "navigation/navigation-list/&search=" + $("#search").val(),
        result: "navigation-list",
        type: "include"
    });
    return false;
}

// Removes a link
function remove_link(id) {
    admin_scroll_top();
    $("#remove-window").show();
	$("#remove-window").html(working());
    theamus.ajax.run({
        url: "navigation/remove-link&id=" + id,
        result: "remove-window",
        type: "include"
    });

	return false;
}

function close_remove_link() {
    $("#remove-window").html("");
    $("#remove-window").hide();

    return false;
}

function submit_remove_link() {
    $("#remove_result").html(working());
    theamus.ajax.run({
        url: "navigation/remove/",
        result: "remove_result",
        extra_fields: "link_id",
        hide_result: 3,
        after: {
            do_function: ["close_remove_link", "search_nav"]
        }
    });

    return false;
}

function next_page(page) {
    $("#navigation-list").html(working());
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

$(document).ready(function() {
    load_nav();
});