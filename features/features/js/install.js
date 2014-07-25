function install_feature() {
    $("#install-result").html(alert_notify('spinner', 'Working...'));
    theamus.ajax.run({
        url:    "features/install/install/",
        result: "install-result",
        form:   "feature_install-form",
        after:  function() {
            $("#feature_install-button").attr("disabled", true);
            setTimeout(function() {
                update_admin_window_content('theamus-features', 'features/');
                change_admin_window_title('theamus-features', 'Theamus Features');
            }, 1500);
        }
    });
}

function upload_listen() {
    $("[name='file']").change(function(e) {
        theamus.ajax.run({
            url: "features/install/prelim/",
            result: "prelim-notes",
            form: "feature_install-form",
            after: function() {
                $("#feature_prelim-info-wrapper").show();
                $("[name='file']").prop("disabled", "true");
            }
        });
    });
}

function back_to_list() {
    countdown("Back to list in", 3);
            setTimeout(function() {
                update_admin_window_content('theamus-features', 'features/');
                change_admin_window_title('theamus-features', 'Theamus Features');
            }, 1500);
}

$(document).ready(function() {
    $("#feature_install-form").submit(function(e) {
        e.preventDefault();
        install_feature();
    });

    upload_listen();
});
