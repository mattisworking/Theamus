<!-- Navigation Tabs -->
<div class='admin-tabs'><?php echo $Navigation->navigation_tabs(FILE); ?></div>

<!-- Navigation form result -->
<div id='navigation-result' style='margin-top: 15px;'></div>

<!-- New navigation item form -->
<form class='form' style='width: 600px;' id='link-form'>
    <input type='hidden' name='page-type' value='create'>
    <h2 class='form-header'>Link Text</h2>
    <div class='form-group'>
        <input type='text' class='form-control' name='text' id='text' autocomplete='off'>
    </div>

    <h2 class='form-header'>Link Path</h2>
    <div class='col-12'>
        <div class='col-4'>
            <input type='hidden' name='path-type' id='path-type' value='path-url' />
            <ul style='margin: 0; padding: 0;'>
                <li><a href='#' name='path' id='path-url'>Website URL</a></li>
                <li><a href='#' name='path' id='path-page'>Theamus Page</a></li>
                <li><a href='#' name='path' id='path-feature'>Theamus Feature</a></li>
                <li><a href='#' name='path' id='path-js'>Javascript</a></li>
                <li><a href='#' name='path' id='path-null'>Text Only</a></li>
            </ul>
        </div>

        <div class='col-8'>
            <!-- Custom URL -->
            <div id='path-url-wrapper' class='form-group'>
                <label class='control-label' for='url-path'>URL Path</label>
                <input type='text' class='form-control' name='url' id='url-path' autocomplete='off'>
            </div>

            <!-- Theamus Page -->
            <div id='path-page-wrapper' class='form-group' style='display: none;'>
                <label class='control-label' for='page-select'>Theamus Page</label>
                <select class='form-control' name='page' id='page-select'></select>
            </div>

            <!-- Theamus Feature -->
            <div id='path-feature-wrapper' class='form-group' style='display: none;'>
                <label class='control-label' for='feature-select'>Theamus Feature</label>
                <select class='form-control' name='feature' id='feature-select'></select>

                <hr class='form-split'>

                <label class='control-label' for='feature-file-select'>Feature File</label>
                <select class='form-control' name='file' id='feature-file-select'></select>
            </div>

            <!-- Javascript -->
            <div id='path-js-wrapper' class='form-group' style='display: none;'>
                <label class='control-label' for='js'>Javascript</label>
                <input type='text' class='form-control' name='js' id='js' autocomplete='off'>
            </div>

            <!-- Nolink -->
            <div id='path-null-wrapper' class='form-group' style='display: none;'>
                <input type='hidden' name='null' value='null' />
                <p class='form-control-help'>Creating a link that is 'Text Only' will do exactly what you think it will.</p>
            </div>
        </div>
    </div>

    <h2 class='form-header'>Link Positioning</h2>
    <div class='form-group'>
        <label class='control-label col-3' for='position'>Theme Position</label>
        <div class='col-9'>
            <select class='form-control' name='position' id='position'>
                <?=$Navigation->get_positions_select($post['position'])?>
            </select>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='child-of'>Child of</label>
        <div class='col-9'>
            <select class='form-control' name='child_of' id='child-of'>
                <?=$Navigation->get_children_select($post['child_of'])?>
            </select>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-3' for='weight'>Link Weight</label>
        <div class='col-9'>
            <select class='form-control' name='weight' id='weight'>
                <?php for ($i=1; $i<=100; $i++): ?>
                <option value='<?php echo $i; ?>'><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>


    <h2 class='form-header'>Permissions</h2>
    <div class='form-group'>
        <label class='control-label col-3' for='group-select'>Groups</label>
        <div class='col-9'>
            <!-- Updated via AJAX -->
            <select class='form-control' name='groups' id='group-select' multiple='multiple' size='13'></select>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Create Link</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_navigation_tab');
    admin_window_run_on_load('load_groups_select');
    admin_window_run_on_load('add_listeners');
    admin_window_run_on_load('load_selects');
</script>