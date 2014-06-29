<?php
// Define the feature alias name
$alias = $id = ''; // Alias and id by default are blank

// If alias was set in the post
if (isset($_GET['id'])) {
    // If we're looking at an actual string
    if ($_GET['id'] != '') {
        // Define alias
        $id = $_GET['id'];
    }
}

$query_data = array("table" => $tData->prefix."features");

// Find the feature in the database
$query = $tData->select_from_table($query_data['table'], array("alias"), array(
        "operator" => "",
        "conditions" => array("id" => $id)));

// Make sure the feature exists and update the alias
if ($tData->count_rows($query) > 0) {
    // Grab the feature information
    $feature = $tData->fetch_rows($query);

    // Define the new alias
    $alias = $feature['alias'];
}

// Define the path to the feature's view files
$featurePath = path(ROOT . '/features/' . $alias . '/views');

// Get all of the view files
$featureFiles = $tFiles -> scan_folder($featurePath, $featurePath);

// Make sure there are files
if (count($featureFiles) > 0) {
    // Loop through all of the files
    foreach ($featureFiles as $fFile) {
        $hfile = array();
        // Remove the file type
        $file = explode('.', $fFile);

        // Clean up the file name
        $fileName = str_replace('.php', '', $fFile);
        $fileName = str_replace('/', ': ', $fileName);
        $fileName = str_replace('\\', ': ', $fileName);
        $fileName = str_replace('_', ' ', $fileName);
        $fileName = str_replace('-', ' ', $fileName);
        $fileName = ucwords($fileName);

        if ($homeFeature['file'] != '') {
            $hfile['selected'] = $homeFeature['file'].'.php' == $fFile ? 'selected' : '';
        } else if ($fFile == 'index.php') {
            $hfile['selected'] = 'selected';
        } else {
            $hfile['selected'] = '';
        }
        // Create the option
        echo '<option value="' . $file[0] . '" ' . $hfile['selected'] . '>' . $fileName . '</option>';
    }
} else {
    echo '<option>There aren\'t any files.</option>';
}
?>