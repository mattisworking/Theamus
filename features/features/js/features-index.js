function load_features() {
    $("#feature-list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url: "features/features-list/",
        result: "feature-list",
        type: "include",
        after: function() {list_listeners();}
    });
    return false;
}

function search_features() {
    $("#feature-list").html(alert_notify('spinner', 'Searching...'));
    theamus.ajax.run({
        url: "features/features-list/&search=" + $("#search").val(),
        result: "feature-list",
        type: "include",
        after: function() {list_listeners();}
    });
    return false;
}

// Removes a feature
function remove_feature(id) {
    admin_scroll_top();
    $("#remove-window").show();
	$("#remove-window").html(alert_notify('spinner', 'Working...'));
    theamus.ajax.run({
        url: "features/remove-feature&id=" + id,
        result: "remove-window",
        type: "include"
    });

	return false;
}

function close_remove_feature() {
    $("#remove-window").html("");
    $("#remove-window").hide();

    return false;
}

function submit_remove_feature() {
    $("#remove_result").html(working());
    theamus.ajax.run({
        url: "features/remove/",
        result: "remove-result",
        extra_fields: "feature_id",
        hide_result: 3,
        after: function() {
            setTimeout(function() {
                update_admin_window_content('theamus-features', 'features/');
                change_admin_window_title('theamus-features', 'Theamus Features');
            }, 1500);
        }
    });

    return false;
}

function features_next_page(page) {
    $("#users_list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url:    "features/features-list/&search=" + $("#search").val() + "&page=" + page,
        result: "feature-list",
        type:   "include",
        after: function() {list_listeners();}
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
        update_admin_window_content('theamus-features', 'features/remove-feature?id='+$(this).data('id'));
    });
}