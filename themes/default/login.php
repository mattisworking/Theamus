<!DOCTYPE html>
<html>
    <head>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $Theamus->Theme->get_page_variable("base"); ?>
        <title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
        <?php echo $Theamus->Theme->get_page_variable("css"); ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>/css/login.css" />
    </head>
    <body>
        <header class="login_header">
            <span class="login_header-company"><?php echo $Theamus->Theme->get_system_variable("name"); ?></span>
        </header>
        <div class="login_area-wrapper">
            <div class="login_area"><?php $Theamus->Theme->content(); ?></div>
            <div class="login_site-link">
                <a href="./">< Back to <?php echo $Theamus->Theme->get_system_variable("name"); ?></a>
            </div>
        </div>
        <?php echo $Theamus->Theme->get_page_variable("js"); ?>
    </body>
</html>