function change_path(to) {
    // Hide the old element
    $("#"+$("#path-type").val()+"-wrapper").hide();

    // Update the type element
    $("#path-type").val(to);

    // Show the new element
    $("#"+to+"-wrapper").show();

    // Load the selects
    load_selects();
}

function load_selects() {
    load_pages_select();        // Load page options
    load_features_select();     // Load feature options
}

function load_pages_select() {
    // Check for a valid DOM element
    if ($("#page-select").length > 0) {
        theamus.ajax.run({
            url:    "navigation/selects/pages&page="+encodeURIComponent($("#page").val()),
            result: "page-select",
            type:   "include"
        });
    }
}

function load_features_select() {
    // Check for a valid DOM element
    if ($("#feature-select").length > 0) {
        theamus.ajax.run({
            url:    "navigation/selects/features/&feature="+$("#feature").val(),
            result: "feature-select",
            type:   "include",
            after:  {
                do_function: "load_feature_files_select"
            }
        });
    }
}

function load_feature_files_select() {
    // Define the feature for the files we need to load
    var feature = $("#feature-select").val();

    // Check for a valid DOM element
    if ($("#feature-file-select").length > 0) {
        theamus.ajax.run({
            url:    "navigation/selects/feature-files/&alias="+feature+"&file="+$("#feature-file").val(),
            result: "feature-file-select",
            type:   "include"
        });
    }
}

function load_groups_select() {
    // Check for a valid DOM element
    if ($("#group-select").length > 0) {
        theamus.ajax.run({
            url:    "navigation/selects/groups/&groups="+$("#groups").val(),
            result: "group-select",
            type:   "include"
        });
    }
}

function load_form() {
    // Get the form type
    var type = $("#page-type").val();

    // Load the form
    theamus.ajax.run({
        url:    "navigation/form/&type="+type,
        result: "form-wrapper",
        type:   "include",
        form:   "info-form",
        after:  {
            do_function: ["load_groups_select", "add_listeners", "load_selects"]
        }
    });
}

function add_listeners() {
    // Allow the user to go back
    $("[name='cancel']").click(function() {
        admin_go('pages', 'navigation/');
    });

    // Handle the changing of path types
    $("[name='path']").click(function(e) {
        e.preventDefault();
        change_path(this.id);
    });

    // Load the appropriate files for a selected feature
    $("#feature-select").change(function() {
        load_feature_files_select();
    });

    // Submit the form
    $("#link-form").submit(function(e) {
        e.preventDefault();

        // Scroll up!
        $('#theamus-navigation').scrollTop(0);

        // Get the type of form we need to submit
        var type = $("[name='page-type']").val();

        $(this).find('button').attr('disabled', 'disabled');

        theamus.ajax.run({
            url:    "navigation/"+type+"/",
            result: "navigation-result",
            form:   "link-form",
            after: function() {
                if (type === 'save') {
                    $("#link-form").find('button').removeAttr('disabled');
                }
            }
        });
    });
}

function back_to_navigation_list() {
    // Start a countdown timer
	countdown("Back to the list in", 3);

    // Go back to the list after three seconds
	setTimeout(
		function() {
			change_admin_window_title('theamus-navigation', 'Theamus Navigation');
            update_admin_window_content('theamus-navigation', 'navigation/');
		}, 3000);
}