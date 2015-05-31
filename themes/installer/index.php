<!doctype html>
<html>
    <head>
        <meta charset='UTF-8'>
        <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>
        <?php echo $Theamus->Theme->get_page_variable('base'); ?>
        <title><?php echo $Theamus->Theme->get_page_variable('title'); ?></title>
        <?php echo $Theamus->Theme->get_page_variable('css'); ?>
        <link href='<?php echo $Theamus->Theme->get_page_variable('theme_path'); ?>style/css/main.css' rel='stylesheet' type='text/css'>
    </head>

    <body>
        <div class='header'>
            <div class='logo'>
                <img src='<?php echo $Theamus->Theme->get_page_variable('theme_path'); ?>images/theamus-logo.svg' alt='theamus'>
            </div>
            <div class='page-header'>
                <?php echo $Theamus->Theme->get_page_variable('header'); ?>
            </div>
        </div>

        <?php $Theamus->Theme->content(); ?>
        <?php echo $Theamus->Theme->get_page_variable('js'); ?>
    </body>
</html>