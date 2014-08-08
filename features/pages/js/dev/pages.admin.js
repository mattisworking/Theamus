function change_pages_tab() {
    $('[name="pages-tab"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-pages', $(this).attr('data-file'));
        change_admin_window_title('theamus-pages', $(this).attr('data-title'));
    });
}


function get_pages(page) {
    // Define the page number
    var page_number = page === undefined || (page % 1) !== 0 ? 1 : page,
        search_query = $('#pages-search-query').length > 0 ? $('#pages-search-query').val() : '';

    // Notify the user that the loading is going on
    $('#pages_list').html(alert_notify('spinner', 'Loading...'));

    // Make the call to get the information
    Theamus.Ajax.run({
        url: 'pages/pages-list/&search='+search_query+'&page='+page_number,
        result: "pages-list",
        type: "include",
        after: function() {
            list_listeners();
        }
    });

    return false; // Prevent the links from going anywhere
}


function list_listeners() {
    // Edit pages link
    $('[name="edit-page-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-pages', 'Edit Page');
        update_admin_window_content('theamus-pages', 'pages/edit?id='+$(this).data('id'));
    });

    // Delete pages link
    $('[name="remove-page-link"]').click(function(e) {
        e.preventDefault();

        change_admin_window_title('theamus-pages', 'Remove Page');
        update_admin_window_content('theamus-pages', 'pages/remove?id='+$(this).data('id'));
    });
}


function search() {
    $('#pages-search-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        get_pages(1); // Search for pages
    });
}


function add_new_link() {
    var count = $("#link-area").children().length + 1,
        remove = "";

    if (count > 1) {
        remove = "<div class='form-control-static'>"+
                     "<a href='#' data-link='"+count+"' name='remove-link'>Remove</a>"+
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


function aggregate_navigation() {
    var children = $("#link-area").children(),
        collection = new Array(),
        ret;

    for (var i = 1; i <= children.length; i++) {
        if ($("#linktext-"+i).val() !== "") collection.push($("#linktext-"+i).val()+"::"+$("#linkpath-"+i).val());
    }

    ret = collection.join(",");
    $("#navigation").val(ret);

    return;
}


function remove_link() {
    $('[name="remove-link"]').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Remove the link
        $("#link_row"+$(this).attr('data-link')).remove();
    });
}


function load_layout_navigation() {
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
}


function create_page() {
    $('[name="layout"]').change(function() {
        load_layout_navigation();
    });

    $('#add-new-link').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing
        add_new_link();
        remove_link(); // Add the listeners
    });

    // Create page form submit
    $('#create-page-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.

        aggregate_navigation(); // Define the navigation!

        // Notify the user and make them see it
        $('#page-result').html(Theamus.Notify('spinner', 'Creating...'));
        $('#theamus-pages').scrollTop(0);

        // Disable the create button to prevent multi clicks
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        // Make the call to create the page
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/pages/',
            method: ['Pages', 'create_page'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#page-result').html(Theamus.Notify('danger', data.error.message));
                    $('#create-page-form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    $('#page-result').html(Theamus.Notify('success', 'Page created successfully.'));

                    // Go back to the list of pages
                    setTimeout(function() {
                        update_admin_window_content('theamus-pages', 'pages/');
                        change_admin_window_title('theamus-pages', 'Theamus Pages');
                    }, 1500);
                }
            }
        });
    });
}


function edit_page() {
    $('[name="layout"]').change(function() {
        load_layout_navigation();
    });

    $('#add-new-link').click(function(e) {
        e.preventDefault(); // Go nowhere, do nothing
        add_new_link();
        remove_link(); // Add the listeners
    });

    // Save page form submit
    $('#save-page-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing.

        aggregate_navigation(); // Define the navigation!

        // Notify the user and make them see it
        $('#page-result').html(Theamus.Notify('spinner', 'Saving...'));
        $('#theamus-pages').scrollTop(0);

        // Disable the create button to prevent multi clicks
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        // Make the call to create the page
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/pages/',
            method: ['Pages', 'save_page'],
            data: { form: this },
            success: function(data) {
                $('#page-result').show();

                if (data.error.status === 1) {
                    $('#page-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#page-result').html(Theamus.Notify('success', 'Page saved.'));

                    setTimeout(function() { $('#page-result').html('').hide(); }, 2000);
                }

                $('#save-page-form').find('button[type="submit"]').removeAttr('disabled');
            }
        });
    });
}


function remove_page() {
    $('#remove-page-form').submit(function(e) {
        e.preventDefault(); // Go nowhere, do nothing

        // Notify the user and make them see it
        $('#remove-result').html(Theamus.Notify('spinner', 'Removing...'));

        // Disable the create button to prevent multi clicks
        $(this).find('button[type="submit"]').attr('disabled', 'disabled');

        // Make the call to create the page
        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/pages/',
            method: ['Pages', 'remove_page'],
            data: { form: this },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#remove-result').html(Theamus.Notify('danger', data.error.message));
                } else {
                    $('#remove-result').html(Theamus.Notify('success', 'Page removed.'));

                    // Go back to the list of pages
                    setTimeout(function() {
                        update_admin_window_content('theamus-pages', 'pages/');
                        change_admin_window_title('theamus-pages', 'Theamus Pages');
                    }, 1500);
                }
            }
        });
    });
}