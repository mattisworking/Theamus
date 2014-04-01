<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <?php echo $tTheme->get_page_variable("base"); ?>
        <title><?php echo $tTheme->get_page_variable("title"); ?></title>
        <?php echo $tTheme->get_page_variable("css"); ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $tTheme->get_page_variable("theme_path"); ?>/css/main.css" />
        <?php echo $tTheme->get_page_variable("js"); ?>
    </head>
    <body>
        <?php $tTheme->get_page_area("admin"); ?>
        <div class="site_wrapper">
            <?php $tTheme->get_page_area("header"); ?>
            <div class="site_content">
                <?php
                $tTheme->get_page_area("extra-nav");
                $tTheme->get_page_area("body");
                ?>
            </div>
        </div>
    </body>
</html>
