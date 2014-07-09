function show_nav_options() {
    var children = $("[name='layout']").children(),
        select = $("[name='layout']"),
        show = false;
    if (select.length > 0) select = select[0].value;
    else return;

    for (var i = 0; i < children.length; i++) {
        if (children[i].value === select) {
            if (children[i].dataset['nav'] === "true") show = true;
        }
    }

    if (show === false) {
        $("#nav-links").hide();
        $("#link-area").html("");
        $("#navigation")[0].value;
    } else {
        $("#nav-links").show();
        if ($("#link-area").children().length === 0) {
            add_new_link();
        }
    }

    return false;
}

function add_new_link() {
    var count = $("#link-area").children().length + 1,
            remove = "";
    if (count > 1) {
        remove = "<div class='form-control-static'>"+
                     "<a href='#' onclick=\"return remove_link('"+count+"');\">Remove</a>"+
                 "</div>";
    }
    var link = "<div class='link_row' id='link_row"+count+"'>"+
                    "<div class='form-group'>"+
                        "<div class='col-12'>"+
                            "<input type='text' class='form-control' autocomplete='off' placeholder='Link Text' id='linktext-"+count+"' />"+
                            "<input type='text' class='form-control' autocomplete='off' placeholder='Link Path' id='linkpath-"+count+"' />"+
                        "</div>"+
                    "</div>"+ remove +
                "</div>";
    $("#link-area").append(link);
    return false;
}

function remove_link(count) {
    $("#link_row"+count).remove();
    return false;
}

function aggregate_links() {
    var children = $("#link-area").children(),
            collection = new Array(),
            ret;

    for (var i = 1; i <= children.length; i++) {
        if ($("#linktext-"+i).val() !== "") collection.push($("#linktext-"+i).val()+"::"+$("#linkpath-"+i).val());
    }

    ret = collection.join(",");
    $("#navigation").val(ret);
    return false;
}

function create_page() {
    aggregate_links();
    $('#theamus-pages').scrollTop(0);
    $("#page-result").html(alert_notify('spinner', 'Creating this page...'));
    theamus.ajax.run({
        url: "pages/create/",
        result: "page-result",
        form: "page-form",
        extra_fields: "content",
        after: function() {
            $('#page-result').css('padding-top', '15px');
            $('#theamus-pages').find('button').attr('disabled', 'disabled');
        }
    });

    return false;
}

function remove_image_editing() {
    var imgs = $("#rta-input img");

    for (var i = 0; i < imgs.length; i++) {
        imgs[i].removeAttribute("onclick");
    }
}

function save_page() {
    remove_image_editing();
    aggregate_links();
    $('#theamus-pages').scrollTop(0);
    $("#page-result").html(alert_notify('spinner', 'Saving information...'));
    theamus.ajax.run({
        url: "pages/save/",
        result: "page-result",
        form: "page-form",
        extra_fields: "content",
        after: function() {
            $('#page-result').css('padding-top', '15px');
        }
    });

    return false;
}

function back_to_pagelist() {
	countdown("Back to pages list in", 3);
    setTimeout(function() {
        change_admin_window_title('theamus-pages', 'Theamus Pages');
        update_admin_window_content('theamus-pages', 'pages/index/');
    }, 3000);
}

$(document).ready(function() {
    show_nav_options();
});