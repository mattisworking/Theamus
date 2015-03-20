<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $Theamus->Theme->get_page_variable("base"); ?>
        <title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
        <?php echo $Theamus->Theme->get_page_variable("css"); ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>/css/error.css" />
        <link rel="icon" href="<?php echo $Theamus->Theme->get_page_variable("favicon"); ?>">
    </head>
    <body>
        <div class="main">
            <div class="centered">
                <div class="frown">:(</div>
                <div class="text">
                    <span class="type"><?php echo $Theamus->Theme->get_page_variable("error_type"); ?></span>
                    <div class="errortext">
                        Just in case you are unaware -- that's an error.<br />
                        <a href="<?php echo $Theamus->base_url; ?>" id="go-back">Go back to the site</a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <?php echo $Theamus->Theme->get_page_variable("js"); ?>
    </body>
</html>