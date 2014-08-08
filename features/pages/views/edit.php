<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<?php

// Define the page ID
$id = filter_input(INPUT_GET, 'id');

// Try to get the page information
try { $page = $Pages->get_page($id); }
catch (Exception $ex) { die($Theamus->notify('danger', $ex->getMessage())); }

?>

<div id='page-result' style='margin-top: 15px;'></div>


<!-- Edit Page Form -->
<form class='form' id='save-page-form' style='width: 800px;'>
    <input type='hidden' name='id' value='<?=$id?>' />

    <!-- Title -->
    <h2 class='form-header'>Page Title</h2>
    <div class='form-group col-12'>
        <input type='text' class='form-control' id='title' name='title' autocomplete='off' placeholder='e.g. About My Site' value='<?php echo $page['title']; ?>'>
    </div>

    <!-- Content -->
    <h2 class='form-header'>Page Content</h2>
    <div class='form-group'>
        <div class='col-12'>
            <textarea class='form-control monospaced' id='content' name='content'><?php echo $page['raw_content']; ?></textarea>
            <p class='form-control-feedback'>
                Theamus uses <a href='http://parsedown.org/' target='_blank'>Parsedown</a> (and ParsedownExtra) which follows the same syntax as Markdown.<br>
                You can learn about the syntax <a href='http://daringfireball.net/projects/markdown/syntax' target='_blank'>here</a>.
            </p>
        </div>
    </div>

    <!-- Options -->
    <h2 class='form-header'>Page Options</h2>
    <div class='col-12'>
        <div class='col-6'>
            <!-- Theme/Layout -->
            <div class='form-group'>
                <label class='control-label' for='layouts'>Use Theme Layout</label>
                <?=$Pages->get_selectable_layouts($page['theme'])?>
            </div>

            <!-- Permissions -->
            <div class='form-group'>
                <label class='control-label' for='groups'>Permissable Groups</label>
                <select class='form-control' name='groups' id='groups' size='10' multiple='multiple'>
                    <?php echo $Pages->get_group_options(explode(',', $page['groups'])); ?>
                </select>
            </div>
        </div>

        <div class='col-6' id='nav-links' style='display: none;'>
            <input type='hidden' id='navigation' name='navigation' value=''>

            <h2 class='form-header' style='margin-top: 0;'>This layout allows navigation!</h2>
            <div class='form-group'>
                <div id='link-area'>
                    <?php
                    $i = 0; // Initialize the element counter

                    // Loop through all of the navigation items
                    foreach (explode(',', $page['navigation']) as $link_data) {
                        $i++; // Add to the counter

                        $link = explode('::', $link_data); // Define the link information

                        if ($link[0] == '') continue; // Press on if it's blank!
                    ?>

                        <div class='link_row' id='link_row<?=$i?>'>
                            <div class='form-group'>
                                <input type='text' class='form-control' autocomplete='off' placeholder='Link Text' id='linktext-<?php echo $i; ?>' value='<?php echo $link[0]; ?>' />
                                <input type='text' class='form-control' autocomplete='off' placeholder='Link Path' id='linkpath-<?php echo $i; ?>' value='<?php echo $link[1]; ?>' />
                            </div>

                            <?php if ($i > 1): ?>
                            <div class='form-control-static'>
                                <a href='#' data-link='<?php echo $i; ?>' name='remove-link'>Remove</a>
                            </div>
                            <?php endif; ?>

                        </div>

                    <?php
                    }
                    ?>
                </div>
            </div>

            <hr class='form-split'>

            <div class='form-group'>
                <a href='#' id='add-new-link'>Add Another</a>
            </div>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Save Information</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_pages_tab');
    admin_window_run_on_load('edit_page');
    admin_window_run_on_load('remove_link');
    admin_window_run_on_load('load_layout_navigation');
</script>