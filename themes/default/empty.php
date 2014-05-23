<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $tTheme->get_page_variable("base"); ?>
		<title><?php echo $tTheme->get_page_variable("title"); ?></title>
        <?php echo $tTheme->get_page_variable("css"); ?>
		<link rel="stylesheet" href="<?php echo $tTheme->get_page_variable("theme_path"); ?>/css/main.css" />
        <?php echo $tTheme->get_page_variable("js"); ?>
    </head>
    <body>
        <?php echo $tTheme->content(); ?>
    </body>
</html>