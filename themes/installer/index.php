<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $Theamus->Theme->get_page_variable("base"); ?>
        <title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
        <?php echo $Theamus->Theme->get_page_variable("css"); ?>
        <link href="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>style/css/main.css" rel="stylesheet" type="text/css">
        <?php echo $Theamus->Theme->get_page_variable("js"); ?>
    </head>

    <body>
        <div class="header">
            <div class="logo">
                <img src="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>images/theamus-logo.svg" alt="theamus">
            </div>
            <div class="page-header">
                <?php echo $Theamus->Theme->get_page_variable("header"); ?>
                <a href="#" id="step-chooser"><span class="caret"></span></a>
            </div>
        </div>

        <?php $Theamus->Theme->content(); ?>

        <script type="text/javascript">
            $(function() {
                $("#step-chooser").click(function(e) {
                    e.preventDefault();

                    // Define the modal background and the chooser window
                    var modal_background    = $("#modal-background"),
                        chooser_window      = $("#chooser-window"),
                        removed             = false; // Let's us know whether or not to show/hide a new window

                    // Remove the modal background if there is one
                    if (modal_background.length > 0) {
                        modal_background.remove();
                        removed = true;
                    }

                    // Remove the chooser window if there is one
                    if (chooser_window.length > 0) {
                        chooser_window.remove();
                        removed = true;
                    }

                    if (removed === false) {
                        // Create the modal background
                        var mbg = document.createElement("div");
                        mbg.setAttribute("style", "background-color: rgba(255, 255, 255, 0.7); position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 10");
                        mbg.setAttribute("id", "modal-background");
                        document.body.appendChild(mbg);

                        // Create the chooser window
                        var cw = document.createElement("div");
                        cw.setAttribute("style", "background-color: #fff; border: 2px solid #ccc; box-shadow: 0 0 10px #aaa; position: fixed; top: 50px; padding: 20px; width: 300px; z-index: 15");
                        cw.setAttribute("id", "chooser-window");
                        document.body.appendChild(cw);

                        // Define the links available to the user
                        var links = [
                                {"text": "Welcome", "path": Theamus.base_url, "step": "welcome"},
                                {"text": "Dependencies Check", "path": Theamus.base_url+"install/dependencies-check/", "step": "dependencies-check"},
                                {"text": "Database Configuration", "path": Theamus.base_url+"install/database-configuration", "step": "database-configuration"},
                                {"text": "Site Customization and Security", "path": Theamus.base_url+"install/customization-and-security/", "step": "customization-and-security"},
                                {"text": "First User Setup", "path": Theamus.base_url+"install/first-user-setup/", "step": "first-user-setup"},
                                {"text": "Advanced Install Options", "path": Theamus.base_url+"install/advanced-options", "step": "advanced-options"},
                                {"text": "Review and Install", "path": Theamus.base_url+"install/review-and-install", "step": "review-and-install"}
                            ],
                            step_links  = [],
                            passed_steps = localStorage.getItem("step"),
                            current_step = localStorage.getItem("current_step");

                        // Define the passed steps, if any
                        passed_steps = passed_steps !== null ? JSON.parse(passed_steps) : [];

                        // Define the current step, if any
                        current_step = current_step !== null ? current_step : "welcome";

                        for (var i = 0; i < links.length; i++) {
                            if (passed_steps.indexOf(links[i].step) !== -1 || links[i].step === current_step || (links[i].step === "advanced-options" && current_step === "review-and-install")) {
                                step_links.push("<li style='padding: 10px;'><a href='"+links[i].path+"'>"+links[i].text+"</a></li>");
                            } else {
                                step_links.push("<li style='padding: 10px;'>"+links[i].text+"</li>");
                            }
                        }

                        // Add the data to the chooser window and center it
                        var cw_html = ["<div style='border-bottom: 1px solid #eee; padding: 5px;'><div style='float: right; font-size: 2em;'><a href='#' style='color: #ccc; text-decoration: none;' id='close-chooser-window'>&times;</a></div><div style='display: inline-block; font-family: Roboto, sans-serif; font-size: 1.5em;'>Step Chooser</div></div>"];
                        cw_html.push("<div style='padding: 10px 0;'><ul style='list-style: none; margin: 0; padding: 0;'>"+step_links.join(" ")+"</ul>");
                        $(cw).html(cw_html.join(""));
                        $(cw).center();

                        // Handle the close button (the "X")
                        $("#close-chooser-window").click(function(e) {
                            e.preventDefault();

                            $("#modal-background").remove();
                            $("#chooser-window").remove();
                        });

                        // Handle a close if the modal background was clicked
                        $("#modal-background").click(function() {
                            $("#modal-background").remove();
                            $("#chooser-window").remove();
                        });
                    }
                });
            });
        </script>
    </body>
</html>