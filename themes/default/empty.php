<!DOCTYPE html>
<html>
    <head>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $tTheme->get_page_variable("base"); ?>
		<title><?php echo $tTheme->get_page_variable("title"); ?></title>
        <?php echo $tTheme->get_page_variable("css"); ?>
		<link rel="stylesheet" href="<?php echo $tTheme->get_page_variable("theme_path"); ?>/css/main.css" />
    </head>
    <body>
        <?php echo $tTheme->content(); ?>
        <?php echo $tTheme->get_page_variable("js"); ?>
    </body>
</html>