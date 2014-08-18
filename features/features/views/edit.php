<div class='admin-tabs'><?php echo $Features->features_tabs(FILE); ?></div>

<?php

try {
    $feature = $Features->get_feature(filter_input(INPUT_GET, 'id'));
    $Features->get_check_config($Theamus->file_path(ROOT.'/features/'.$feature['alias']), true);
}
catch (Exception $ex) { die($Theamus->notify('danger', $ex->getMessage())); }

?>

<div id='feature_edit-result' style='margin-top: 15px;'></div>

<form class='form' id='feature_edit-form' style='width: 700px;'>
    <div class='form-group'>
        <h2 class='form-header'>Feature Update Files</h2>
        <div class='col-12'>
            <input type='file' class='form-control' name='file'>
            <p class='form-control-feedback'>
                Features should come in the form of zip archives.<br />
                Select the feature you want to install and everything will be handled automatically from there.
            </p>
        </div>
    </div>

    <h2 class='form-header'>Feature Accessibility</h2>
    <div class='form-group'>
        <label class='control-label col-3' for='group-select'>Allowed Groups</label>
        <div class='col-9'>
            <select class='form-control' name='groups' id='group-select' multiple='multiple' size='12'>
                <?php echo $Features->get_groups($feature['groups']); ?>
            </select>
        </div>
    </div>

    <div class='form-group'>
        <label class='checkbox' style='margin: 0;'>
            <input type='checkbox' name='enabled' id='enabled' <?php echo $feature['enabled'] == '1' ? 'checked' : ''; ?>>
            Enabled Feature?
        </label>
    </div>

    <?php if (isset($Features->feature_config['feature_version']) || isset($Features->feature_config['release_notes'])): ?>
    <h2 class='form-header'>Feature Version/Notes</h2>
    <?php endif; ?>

    <?php if (isset($Features->feature_config['feature_version'])): ?>
    <div class='form-group'>
        <label class='control-label col-3'>Feature Version</label>
        <div class='col-9'><?php echo $Features->feature_config['feature_version']; ?></div>
    </div>
    <?php endif; ?>

    <?php if (isset($Features->feature_config['release_notes'])): ?>
    <div class='form-group'>
        <label class='control-label col-3'>Feature Notes</label>
        <div class='col-9'>
            <?php
            $feature_notes = $Features->feature_config['release_notes'];

            echo '<ul class="feature-notes">';
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
            echo '</ul>';
            ?>
        </div>
    </div>
    <?php endif; ?>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Save Information</button>
    </div>

    <input type='hidden' name='id' value='<?php echo $feature['id']; ?>'>
</form>

<script>
    admin_window_run_on_load('change_features_tab');
    change_admin_window_title('theamus-features', 'Edit Feature "<?php echo $feature['name']; ?>"');
    admin_window_run_on_load('edit_feature');
</script>