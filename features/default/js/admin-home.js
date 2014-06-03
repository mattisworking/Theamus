function open_home_prefs() {
    $("#choice-window").show();
    theamus.ajax.run({
        url: "default/admin/choice-window/",
        result: "choice-window",
        type: "include"
    });

    return false;
}

function close_home_prefs() {
    $("#choice-window").hide();
    $("#choice-window").html("");

    return false;
}

function save_home() {
    var apps = $("[name='homeapp']");
    var homeapps = new Array();
    for (var i = 0; i < apps.length; i++) {
        var check = apps[i].checked === true ? 1 : 0;
        homeapps.push(apps[i].id + "=" + check);
    }
    homeapps.join(",");

    theamus.ajax.run({
        url: "default/admin/save-home/?apps=" + homeapps,
        result: "home-result",
        after: function() {
            update_admin_window_content('theamus-dashboard', 'default/adminHome/');
        }
    });

    return false;
}

function refresh() {
    countdown("Refreshing in", 3);
    setTimeout(function() {
        theamus.ajax.run({
            url: "default/admin/apps/",
            result: "apps",
            type: "include",
            after: {
                do_function: ["add_admin_extras", "enable_sort"]
            }
        });
        close_home_prefs();
    }, 3000);
}

function update_order(column_id) {
    // Define variables
    var column, i, item, info;

    // Get the column children
    column = $("#" + column_id).children();

    // Loop through all of the children
    for (i = 0; i < column.length; i++) {
        item = column[i];                   // Simplify the column item
        info = item.id.split("=");          // Get the item name
        item.id = info[0] + "=" + (i + 1);  // Reset the item name, with organization
    }

    return false;
}

function get_item_positions(column_id) {
    // Define variables
    var column, i, ids = new Array();

    // Get the column children
    column = $("#" + column_id).children();

    // Loop through all of the children
    for (i = 0; i < column.length; i++) {
        ids.push(column[i].id); // Add this id to the return array
    }

    ids.join(","); // Join the array into a string
    return ids;
}

function set_url_positions(column_array) {
    // Define variables
    var i, info = new Array();

    // Loop through the columns
    for (i = 0; i < column_array.length; i++) {
        // Add the column information to an array
        info.push(column_array[i] + "=" + get_item_positions(column_array[i]));
    }

    // Join the array and return it as a string
    info = info.join("&");
    return info;
}

function save_home_layout() {
    update_order("column1");
    update_order("column2");
    var url_info = set_url_positions(["column1", "column2"]);

    theamus.ajax.run({
        url: "default/admin/save-positions/&" + url_info,
        result: "home-result",
        hide_result: 3
    });

    return false;
}

function enable_sort() {
    if (typeof $(".col-half").sortable === "function") {
        $(".col-half").sortable({
            connectWith: ".col-half",
            placeholder: "sortable-placeholder",
            handle: ".handle",
            stop: function(e, ui) {
                save_home_layout();
            }
        }).disableSelection();
    } else {
        setTimeout(enable_sort, 1000);
    }
}

function update_apps() {
    theamus.ajax.run({
        url: "default/admin/update-apps",
        result: "home-result"
    });

    return false;
}

$(document).ready(function() {
    enable_sort();
    update_apps();

    $("#choice-window").click(function(e) {
        e.stopPropagation();
    });
});

$(document).click(function() {
    close_home_prefs();
});