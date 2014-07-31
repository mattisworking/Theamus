<!DOCTYPE html>
<html>
    <head>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $Theamus->Theme->get_page_variable("base"); ?>
		<title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
        <?php echo $Theamus->Theme->get_page_variable("css"); ?>
		<link rel="stylesheet" href="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>/css/main.css" />
    </head>
    <body>
        <?php echo $Theamus->Theme->content(); ?>
        <?php echo $Theamus->Theme->get_page_variable("js"); ?>
    </body>
</html>