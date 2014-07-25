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
	// Define the features and upload temp folder path
	$featurePath = path(ROOT."/features/");
	$tempPath = path(ROOT."/features/features/temp/");

	// Create a nicer filename
	$filename = $file['name'];
	$filename = strtolower($filename);
	$filename = str_replace(" ", "_", $filename);

	// Upload the file and make it writable
	move_uploaded_file($file['tmp_name'], $tempPath.$filename);

	// Create a new zip object
	$zip = new ZipArchive();

	// Open the zip file
	$zip->open($tempPath.$filename);

	// Extract the zip file to a temp folder
	$tempFolder = time();
	mkdir($tempPath.$tempFolder, 0755);
	$zip->extractTo($tempPath.$tempFolder);

	// Check for required files
	$check = "";
	if (!file_exists($tempPath.$tempFolder."/config.php"))
		$check = "The config file does not exist!";
	if (!file_exists($tempPath.$tempFolder."/files.info.php"))
		$check = "The specific information file does not exist!";
	if (!is_dir($tempPath.$tempFolder."/views/"))
		$check = "The 'views' folder doesn't exist!";

	if ($check != "") {
		alert_notify("danger", $check);
	} else {
		// Include the config file
		include $tempPath.$tempFolder."/config.php";

		// Define the features prefix
		$table = $tDataClass->prefix."features";

		// Clean up variables
		isset($feature['folder']) && $feature['folder'] != ""
			? $alias = $tData->real_escape_string($feature['folder'])
			: $error[] = "There's no folder name defined to create the feature with.";

		isset($feature['name']) && $feature['name'] != "" ?
			$name	= $tData->real_escape_string($feature['name'])
			: $error[] = "There is no feature name defined for this feature.";

		if (is_dir($featurePath.$feature['folder']))
			$error[] = "This feature has already been installed.";

		// Database prefix
		$dbprefix = isset($feature['db_prefix']) ? $feature['db_prefix'] : "";
		if ($dbprefix != "") {
			$last = substr($dbprefix, "-1");
			$dbprefix = $last != "_" ? $dbprefix."" : $dbprefix;
		}

		if (!empty($error)) {
			alert_notify("danger", $error[0]);
		} else {
			if (isset($feature['groups'])) {
				$groups = $tData->real_escape_string(implode(",", $feature['groups']));
			} elseif ($groups != "") {
				$groups = $tData->real_escape_string($feature['groups']);
			}

			// Add this entry to the database
			$sql = "INSERT INTO `".$table."` (`alias`, `name`, `groups`,
				`permanent`, `enabled`, `db_prefix`) VALUES ('".$alias."',
				'".$name."', '".$groups."', '0', '1', '".$dbprefix."')";
			$tData->query($sql);

			// Add all permissions to the database
			if (isset($feature['permissions'])) {
				if (is_array($feature['permissions'])) {
					foreach ($feature['permissions'] as $perm) {
						$permission = $tData->real_escape_string($perm);
						$ptable = $tDataClass->prefix."permissions";
						$permSql = "INSERT INTO `".$ptable."` (`feature`,
							`permission`) VALUES ('".$alias."', '".$permission."')";
						$tData->query($permSql);
					}
				}
			}

			// Run the installer sql if it exists
			if (isset($feature['install']['script'])) {
				$scriptpath = $tempPath.$tempFolder."/".$feature['install']['script'];
				if (file_exists($scriptpath)) {
					include $scriptpath;
				}
			}

			// Create the feature folder in the main features directory
			@mkdir($featurePath.$feature['folder'], 0755);

			// Unzip the files to that new folder
			$zip->extractTo($featurePath.$feature['folder']);

			// Success user and go back to list
			alert_notify("success", "Feature installed!<br>".js_countdown());
			run_after_ajax("back_to_list");
		}

		// Remove the created temp folder
		$tFiles->remove_folder($tempPath.$tempFolder);

		// Remove the zip file
		unlink($tempPath.$filename);
	}
}

?>