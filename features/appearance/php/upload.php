<?php

// Error checking
$error = array();

// File selected
array_key_exists("file", $_FILES) ? $file = $_FILES['file']
	: $error = "Please select a file to upload.";

// Check the file extension
$extension = explode(".", $file['name']);
$extension = $extension[count($extension)-1];
if ($extension != "zip") $error[] = "This file type isn't accepted here.";

// Show errors
if (!empty($error)) {
	alert_notify("danger", $error[0]);
} else {
	// Define the themes and upload temp folder path
	$path['themes'] = path(ROOT."/themes/");
	$path['temp']	= path(ROOT."/features/appearance/temp/");

	// Create a nicer file name
	$filename = $file['name'];
	$filename = strtolower($filename);
	$filename = str_replace(" ", "_", $filename);

	// Upload the file and make it writable
	move_uploaded_file($file['tmp_name'], $path['temp'].$filename);

	// Create a new zip object
	$zip = new ZipArchive();

	// Open the zip file
	$zip->open($path['temp'].$filename);

	// Extract the zip file to a temp folder
	$tempfolder = time();
	mkdir($path['temp'].$tempfolder, 0755);
	$zip->extractTo($path['temp'].$tempfolder);

	// Check for required files
	$check = "";
	if (!file_exists($path['temp'].$tempfolder."/config.php"))
		$check = "The configuration file for this theme does not exist!";
	if (!file_exists($path['temp'].$tempfolder."/html.php"))
		$check = "The main html file for this theme does not exist!";

	// Show the check results
	if ($check != "") {
		alert_notify("danger", $check);
	} else {
		// Include the config file
		include $path['temp'].$tempfolder."/config.php";

		// Clean up the variables
		isset($theme['folder']) && $theme['folder'] != ""
			? $alias = $tData->real_escape_string($theme['folder'])
			: $error[] = "There's no folder name defined to create the theme with.";

		isset($theme['name']) && $theme['name'] != ""
			? $name = $tData->real_escape_string($theme['name'])
			: $error[] = "There's no theme name defined.";

		// Show the errors
		if (!empty($error)) {
			alert_notify("danger", $error[0]);
		} else {
			// Define the theme database table
			$table = $tDataClass->prefix."themes";

			// Add this entry to the database
			$sql['add'] = "INSERT INTO `".$table."` (`alias`, `name`, `active`,
				`permanent`) VALUES ('".$alias."', '".$name."', 0, 0)";
			$tData->query($sql['add']);

			// Create the theme folder in the themes directory
			@mkdir($path['themes'].$theme['folder'], 0755);

			// Unzip the files to that new folder
			$zip->extractTo($path['themes'].$theme['folder']);

			// Success user and go back to the list
			$message = "'".$theme['name']."' has been installed!";
			$message .= "<br />".js_countdown();
			alert_notify("success", $message);
			run_after_ajax("back_to_list");
		}
	}

	// Remove the created temp folder
	$tFiles->remove_folder($path['temp'].$tempfolder);

	// Remove the zip file
	unlink($path['temp'].$filename);
}

?>