<!doctype html>
<html>
	<head>
        <?php echo $Theamus->Theme->get_page_variable("base"); ?>
		<title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
        <?php echo $Theamus->Theme->get_page_variable("css"); ?>
		<link rel="stylesheet" href="<?php echo $Theamus->Theme->get_page_variable("theme_path"); ?>css/homepage.css" />
	</head>

	<body>
        <?php echo $Theamus->Theme->get_page_area("admin"); ?>
		<div id="site-wrapper" class="site_wrapper">
			<div class="homepage_wrapper">
				<div class="clouds_wrapper">
					<div class="cloud x1"></div>
					<div class="cloud x2"></div>
					<div class="cloud x3"></div>
					<div class="cloud x4"></div>
					<div class="cloud x5"></div>
				</div>
				<div class="content-wrapper">
					<div class="homepage_header"><?php echo $Theamus->Theme->get_page_variable("header"); ?></div>
					<div class="homepage_content">
						<?php echo $Theamus->Theme->content(); ?>
					</div>
				</div>
			</div>
		</div>

        <?php echo $Theamus->Theme->get_page_variable("js"); ?>
		<script>var wrapper=document.querySelector(".site_wrapper"),wrapper_height=wrapper.offsetHeight,window_height=window.innerHeight,center=window_height/2-wrapper_height/2;wrapper.style.top=center+"px"</script>
	</body>
</html>