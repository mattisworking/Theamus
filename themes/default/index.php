<!DOCTYPE html>
<html>
    <head>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $tTheme->get_page_variable("base"); ?>
        <title><?php echo $tTheme->get_page_variable("title"); ?></title>
        <?php echo $tTheme->get_page_variable("css"); ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $tTheme->get_page_variable("theme_path"); ?>/css/main.css" />
    </head>
    <body>
        <?php $tTheme->get_page_area("admin"); ?>
        <div id="site-wrapper" class="site-wrapper site-wrapper-full">
            <?php $tTheme->get_page_area("header"); ?>
            <div class="content-wrapper">
                <?php $tTheme->get_page_area("body"); ?>
            </div>
        </div>


        <?php echo $tTheme->get_page_variable("js"); ?>
    </body>
</html>
