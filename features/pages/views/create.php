<!-- Pages Tabs -->
<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<!-- Create Page Result -->
<div id="page-result"></div>

<!-- Create Page Form -->
<form class="form" id="page-form" onsubmit="return create_page();" style='width: 800px;'>
    <!-- Title -->
    <h2 class="form-header">Page Title</h2>
    <div class='form-group col-12'>
        <input type='text' class='form-control' id='title' name='title' autocomplete='off' placeholder='e.g. About My Site'>
    </div>

    <!-- Content -->
    <h2 class='form-header'>Page Content</h2>
    <div class='form-group'>
        <div class='col-12'><?php new tEditor(array("id"=>"content")); ?></div>
    </div>

    <!-- Options -->
    <h2 class='form-header'>Page Options</h2>
    <div class='col-12'>
        <div class='col-6'>
            <!-- Theme/Layout -->
            <div class='form-group'>
                <label class='control-label' for='layouts'>Use Theme Layout</label>
                <?php echo $Pages->get_selectable_layouts(); ?>
            </div>

            <!-- Permissions -->
            <div class='form-group'>
                <label class='control-label' for='groups'>Permissable Groups</label>
                <select class='form-control' name='groups' id='groups' size='10' multiple='multiple'>
                <?php
                    // Query the database for groups
                    $query = $tData->select_from_table($tData->prefix."groups", array("alias", "name"));

                    // Loop through all groups, showing as options
                    $results = $tData->fetch_rows($query);
                    foreach ($results as $group) {
                        $selected = $group['alias'] == "everyone" ? "selected" : "";
                        echo "<option ".$selected." value='".$group['alias']."'>".$group['name']."</option>";
                    }
                ?>
                </select>
            </div>

            <!-- Link -->
            <h2 class='form-header'>Link</h2>
            <div class='form-group col-12'>
                <label class='checkbox'>
                    <input type='checkbox' name='create_link' id='create_link'>
                    Create a link along with this page
                </label>
            </div>
        </div>

        <div class='col-6' id='nav-links' style='display: none;'>
            <input type='hidden' id='navigation' name='navigation' value=''>

            <h2 class='form-header' style='margin-top: 0;'>This layout allows navigation!</h2>
            <div class='form-group'>
                <div id='link-area'></div>
            </div>

            <hr class='form-split'>

            <div class='form-group'>
                <a href='#' onclick='return add_new_link();'>Add Another</a>
            </div>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Create Page</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_pages_tab');
</script>