<!DOCTYPE html>
<html>
    <head>
        <?php echo $Theamus->Theme->get_page_variable("base"); ?>
        <title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
        <?php echo $Theamus->Theme->get_page_variable("css"); ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>/css/error.css" />
    </head>
    <body onload="center_main(); listen_go_back();">
        <div id="main" class="main">
            <div class="centered">
                <div class="frown left">:(</div>
                <div class="text left">
                    <span class="type"><?php echo $Theamus->Theme->get_page_variable("error_type"); ?></span>
                    <div class="errortext">
                        Just in case you are unaware -- that's an error.<br />
                        <a href="#" id="go-back">Go back to the site</a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <?php echo $Theamus->Theme->get_page_variable("js"); ?>
        <script>function center_main(){var e,t,n,r;e=document.getElementById("main");t=parseInt(e.offsetHeight);n=parseInt(window.innerHeight);r=n/2-t/2+"px";e.style.marginTop=r}function listen_go_back(){var e;e=document.getElementById("go-back");e.addEventListener("click",function(e){e.preventDefault();window.location = Theamus.base_url},false)}</script>
    </body>
</html>