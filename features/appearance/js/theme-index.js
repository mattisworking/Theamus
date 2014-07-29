function load_themes() {
    theamus.ajax.run({
        url:    "appearance/themes-list/",
        result: "themes_list",
        type:   "include",
        after:  function() {
            list_listeners();
        }
    });
    return false;
}

function search_themes() {
    $("#themes_list").html(working());
    theamus.ajax.run({
        url: "appearance/themes-list/&search=" + $("#search").val(),
        result: "themes_list",
        type: "include"
    });
    return false;
}

// Removes a theme
function remove_theme(id) {
    admin_scroll_top();
    $("#remove-window").show();
    $("#remove-window").html(working());
    theamus.ajax.run({
        url: "appearance/remove-theme&id=" + id,
        result: "remove-window",
        type: "include"
    });

    return false;
}

function close_remove_theme() {
    $("#remove-window").html("");
    $("#remove-window").hide();

    return false;
}

function submit_remove_theme() {
    $("#remove_result").html(alert_notify('spinner', 'Working...'));
    theamus.ajax.run({
        url: "appearance/remove/",
        result: "remove-result",
        extra_fields: "theme_id",
        hide_result: 3,
        after: function() {
            setTimeout(function() {
                change_admin_window_title('theamus-appearance', 'Theamus Themes');
                update_admin_window_content('theamus-appearance', 'appearance/');
            }, 1500);
        }
    });

    return false;
}

function themes_next_page(page) {
    $("#themes_list").html(alert_notify('spinner', 'Loading...'));
    theamus.ajax.run({
        url: "appearance/themes-list&search=" + $("#search").val() + "&page=" + page,
        result: "themes_list",
        type: "include"
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
        update_admin_window_content('theamus-appearance', 'appearance/remove-theme?id='+$(this).data('id'));
    });

    $("[name='activate-theme-link']").click(function(e) {
        e.preventDefault();
        theamus.ajax.run({
            url:            "appearance/set-active&id="+$(this).data("id"),
            result:         "themes_list",
            after:          function() {
                setTimeout(function() {
                    load_themes();
                }, 1000);
            }
        });
    });
}

$(document).ready(function() {
    load_themes();
});