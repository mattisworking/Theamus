function load_users() {
    $("#users_list").html(working());
    theamus.ajax.run({
        url: "accounts/users-list/",
        result: "users_list",
        type: "include"
    });
    return false;
}

function search_users() {
    $("#users_list").html(working());
    theamus.ajax.run({
        url: "accounts/users-list/&search=" + $("#search").val(),
        result: "users_list",
        type: "include"
    });
    return false;
}

// Removes a user
function remove_user(id) {
    admin_scroll_top();
    $("#remove-window").show();
	$("#remove-window").html(working());
    theamus.ajax.run({
        url: "accounts/remove-user&id=" + id,
        result: "remove-window",
        type: "include"
    });

	return false;
}

function close_remove_user() {
    $("#remove-window").html("");
    $("#remove-window").hide();

    return false;
}

function submit_remove_user() {
    $("#remove_result").html(working());
    theamus.ajax.run({
        url: "accounts/remove/",
        result: "remove_result",
        extra_fields: "user_id",
        hide_result: 3,
        after: {
            do_function: ["close_remove_user", "search_users"]
        }
    });

    return false;
}

function next_page(page) {
    $("#users_list").html(working());
    theamus.ajax.run({
        url: "accounts/users-list/&search=" + $("#search").val() + "&page=" + page,
        result: "users_list",
        type: "include"
    });
    return false;
}

$(document).ready(function() {
    //load_users();
});