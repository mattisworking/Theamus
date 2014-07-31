<?php $col = ($tTheme->get_page_navigation("extra") != "") ? "col-9" : ""; ?>
<div class="site_content-header"><?php echo $tTheme->get_page_variable("header"); ?></div>
<div class="col-3 site_content-nav">
    <?php echo $tTheme->get_page_navigation("extra"); ?>
</div>
<div class="site_content-full site_content <?=$col?>">
    <?php $tTheme->content(); ?>
</div>
<div class="clearfix"></div>