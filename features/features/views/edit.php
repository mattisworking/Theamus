<?php

$get            = filter_input_array(INPUT_GET); // Clean the request information
$error          = array(); // Error checking array

$id = ""; // Default ID for check later

// Feature ID
if (isset($get['id']) && $get['id'] != "") {
    $id = $get['id'];
} else {
    $error[] = "Unknown feature ID.";
}

// Check the database for this feature
$query = $tData->select_from_table($tData->prefix."features", array(), array(
    "operator"  => "",
    "conditions"=> array("id" => $id)
));

if ($query != false && $tData->count_rows($query) == 0) {
    $error[] = "There was an error finding this feature in the database.";
} else {
    // Grab all feature information
    $feature = $tData->fetch_rows($query);

    $feature_groups = $feature['groups'];

    $config_path = path(ROOT."/features/".$feature['alias']."/config.php");
    if (file_exists($config_path)) {
        include_once $config_path;
    }

    // Define the enabled checkbox
    $enable_check = $feature['enabled'] == 1 ? "checked" : "";
}

?>

<!-- Features Tabs -->
<div class='admin-tabs'><?php echo $Features->features_tabs(FILE); ?></div>

<?php if (!empty($error)) die(alert_notify('danger', $error[0])); ?>

<!-- Edit feature result -->
<div id="edit-result" style='margin-top: 15px;'></div>

<!-- Feature edit form -->
<form class="form" id="edit-form" style='width: 700px;'>
    <div class='form-group'>
        <h2 class='form-header'>Feature Update Files</h2>
        <input type='file' class='form-control' name='file'>
        <p class='form-control-feedback'>
            Features should come in the form of zip archives.<br />
            Select the feature you want to install and everything will be handled automatically from there.
        </p>
    </div>

    <h2 class='form-header'>Feature Accessibiltiy</h2>
    <div class='form-group'>
        <label class='control-label col-3' for='group-select'>Allowed Groups</label>
        <div class='col-9'>
            <select class='form-control' name='groups' id='group-select' multiple='multiple' size='12'></select>
        </div>
    </div>

    <div class='form-group'>
        <label class='checkbox' style='margin: 0;'>
            <input type='checkbox' name='enabled' id='enabled' <?php echo $enable_check; ?>>
            Enabled Feature?
        </label>
    </div>

    <?php if (isset($feature['version']) || isset($feature['notes'])): ?>
    <h2 class='form-header'>Feature Version/Notes</h2>
    <?php endif; ?>

    <?php if (isset($feature['version'])): ?>
    <div class='form-group'>
        <label class='control-label col-3'>Feature Version</label>
        <div class='col-9'><?php echo $feature['version']; ?></div>
    </div>
    <?php endif; ?>

    <?php if (isset($feature['notes'])): ?>
    <div class='form-group'>
        <label class='control-label col-3'>Feature Notes</label>
        <div class='col-9'>
            <?php
            $feature_notes = $feature['notes'];

            echo "<ul class='feature-notes'>";
            foreach ($feature_notes as $version => $notes) {
                echo '<li>';
                echo '<span class="version">'.$version.'</span>';
                if (is_array($notes)) {
                    echo '<ul>';
                    foreach ($notes as $note) echo '<li>'.$note.'</li>';
                    echo '</ul>';
                } else echo $note;
                echo '</li>';
            }
            echo "</ul>";
            ?>
        </div>
    </div>
    <?php endif; ?>

    <hr class='form-split'>

    <div class="form-button-group">
        <button type='submit' class='btn btn-success'>Save Information</button>
    </div>

    <input type="hidden" id="groups" value="<?php echo $feature_groups; ?>">
    <input type='hidden' name='id' value='<?php echo $feature['id']; ?>'>
</form>

<script>
    admin_window_run_on_load('change_features_tab');
    change_admin_window_title('theamus-features', 'Edit Feature "<?php echo $feature['name']; ?>"');
</script>