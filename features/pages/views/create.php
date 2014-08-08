<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<div id='page-result' style='margin-top: 15px;'></div>

<form class='form' id='create-page-form' style='width: 800px;'>
    <h2 class='form-header'>Page Title</h2>
    <div class='form-group col-12'>
        <input type='text' class='form-control' id='title' name='title' autocomplete='off' placeholder='e.g. About My Site'>
    </div>

    <h2 class='form-header'>Page Content</h2>
    <div class='form-group'>
        <div class='col-12'>
            <textarea class='form-control monospaced' id='content' name='content'></textarea>
            <p class='form-control-feedback'>
                Theamus uses <a href='http://parsedown.org/' target='_blank'>Parsedown</a> (and ParsedownExtra) which follows the same syntax as Markdown.<br>
                You can learn about the syntax <a href='http://daringfireball.net/projects/markdown/syntax' target='_blank'>here</a>.
            </p>
        </div>
    </div>

    <h2 class='form-header'>Page Options</h2>
    <div class='col-12'>
        <div class='col-6'>
            <div class='form-group'>
                <label class='control-label' for='layouts'>Use Theme Layout</label>
                <?php echo $Pages->get_selectable_layouts(); ?>
            </div>

            <div class='form-group'>
                <label class='control-label' for='groups'>Permissable Groups</label>
                <select class='form-control' name='groups' id='groups' size='10' multiple='multiple'>
                    <?php echo $Pages->get_group_options(); ?>
                </select>
            </div>

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
                <a href='#' id='add-new-link'>Add Another</a>
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
    admin_window_run_on_load('create_page');
    admin_window_run_on_load('remove_link');
    admin_window_run_on_load('load_layout_navigation');
</script>