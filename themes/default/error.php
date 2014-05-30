<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <?php echo $tTheme->get_page_variable("base"); ?>
        <title><?php echo $tTheme->get_page_variable("title"); ?></title>
        <?php echo $tTheme->get_page_variable("css"); ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $tTheme->get_page_variable("theme_path"); ?>/css/error.css" />
        <?php echo $tTheme->get_page_variable("js"); ?>
        <script>function center_main(){var e,t,n,r;e=document.getElementById("main");t=parseInt(e.offsetHeight);n=parseInt(window.innerHeight);r=n/2-t/2+"px";e.style.marginTop=r}function listen_go_back(){var e;e=document.getElementById("go-back");e.addEventListener("click",function(e){e.preventDefault();window.location = theamus.base_url},false)}</script>
    </head>
    <body onload="center_main(); listen_go_back();">
        <div id="main" class="main">
            <div class="centered">
                <div class="frown left">:(</div>
                <div class="text left">
                    <span class="type"><?php echo $tTheme->get_page_variable("error_type"); ?></span>
                    <div class="errortext">
                        Just in case you are unaware -- that's an error.<br />
                        <a href="#" id="go-back">Go back to the site</a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </body>
</html>