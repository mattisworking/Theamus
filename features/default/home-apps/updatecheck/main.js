function updatecheck_main() {
    $("#updatecheck_update-link").click(function(e) {
        e.preventDefault();

        create_admin_window("theamus-settings", "Theamus Settings", "/settings/update-check/");
    });
}