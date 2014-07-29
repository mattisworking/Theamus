<?php

// Define a default for the javascript and css files
$feature['js']['file']	= array();
$feature['css']['file']	= array();

// Allow administrators only
if (!isset($_POST['ajax'])) back_up();

$feature['class']['file'] = "appearance.class.php";
$feature['class']['init'] = "Appearance";

define('FILE', $file);

// Customize files
switch ($file) {
	case "index.php":
		$feature['js']['file'][]  = "theme-index.js";
        $feature['css']['file'][] = "main.css";
		break;

	case "install.php":
		$feature['js']['file'][]	= "install.js";
		break;

	case "edit.php":
		$feature['js']['file'][]	= "theme-form.js";
        $feature['css']['file'][]   = "main.css";
		break;
}