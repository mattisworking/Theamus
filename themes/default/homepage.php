<!doctype html>
<html>
	<head>
        <?php echo $tTheme->get_page_variable("base"); ?>
		<title><?php echo $tTheme->get_page_variable("title"); ?></title>
        <?php echo $tTheme->get_page_variable("css"); ?>
		<link rel="stylesheet" href="<?php echo $tTheme->get_page_variable("theme_path"); ?>/css/homepage.css" />
        <?php echo $tTheme->get_page_variable("js"); ?>
	</head>

	<body>
        <?php echo $tTheme->get_page_area("admin"); ?>
		<div class="site_wrapper">
			<div class="homepage_wrapper">
				<div class="clouds_wrapper">
					<div class="cloud x1"></div>
					<div class="cloud x2"></div>
					<div class="cloud x3"></div>
					<div class="cloud x4"></div>
					<div class="cloud x5"></div>
				</div>
				<div class="content-wrapper">
					<div class="homepage_header"><?php echo $tTheme->get_page_variable("header"); ?></div>
					<div class="homepage_content">
						<?php echo $tTheme->content(); ?>
					</div>
				</div>
			</div>
		</div>

		<script>var wrapper=document.querySelector(".site_wrapper"),wrapper_height=wrapper.offsetHeight,window_height=window.innerHeight,center=window_height/2-wrapper_height/2;wrapper.style.top=center+"px"</script>
	</body>
</html>