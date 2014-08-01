<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $Theamus->Theme->get_page_variable("base"); ?>
        <title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
        <?php echo $Theamus->Theme->get_page_variable("css"); ?>
        <link href="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>style/css/homepage.css" rel="stylesheet" type="text/css">
        <?php echo $Theamus->Theme->get_page_variable("js"); ?>
    </head>

    <body>
        <div id="site-wrapper">
            <div id="container">
                <div class="logo">
                    <img src="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>images/theamus-logo.svg" alt="theamus">
                </div>
                <div class="homepage-content">
                    <?php $Theamus->Theme->content(); ?>
                </div>
            </div>

            <script type="text/javascript">
                $(function() {
                    $("#container").addClass("load-in-container");
                    $(".homepage-content").addClass("load-in-homepage");
                });
            </script>
        </div>
    </body>
</html>