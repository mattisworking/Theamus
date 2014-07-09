<?php
$get = filter_input_array(INPUT_GET); // Clean the URL parameters

$error = false; // No errors to start out with
// Check for the existance of a page ID
if (isset($get['id'])) {
    $id = $get['id'];

    // Check the ID has a value
    if ($id != '') {
        // Query the database for the page
        $query = $tData->select_from_table($tData->prefix.'pages', array(), array(
            'operator'  => '',
            'conditions'=> array('id' => $id)
        ));

        // Check for a valid query
        if ($query != false) {
            $page = $tData->fetch_rows($query); // Define the database informations
        } else {
            $error = 'There was an error querying the database for the page.';
        }
    } else {
        $error = 'Invalid ID value.';
    }
} else {
    $error = 'No page ID was found.';
}
?>

<!-- Pages Tabs -->
<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<!-- Create Page Result -->
<div id='page-result'></div>

<?php if ($error != false) die(alert_notify('danger', $error)); ?>

<!-- Edit Page Form -->
<form class='form' id='page-form' onsubmit='return save_page();' style='width: 800px;'>
    <input type='hidden' name='page_id' value='<?=$id?>' />

    <!-- Title -->
    <h2 class='form-header'>Page Title</h2>
    <div class='form-group col-12'>
        <input type='text' class='form-control' id='title' name='title' autocomplete='off' placeholder='e.g. About My Site' value='<?php echo $page['title']; ?>'>
    </div>

    <!-- Content -->
    <h2 class='form-header'>Page Content</h2>
    <div class='form-group'>
        <div class='col-12'><?php new tEditor(array('id'=>'content','text'=>$page['content'])); ?></div>
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
                <?php
                    // Define the page groups
                    $pageGroups = explode(',', $page['groups']);

                    // Query the database for groups
                    $query = $tData->select_from_table($tData->prefix.'groups', array('alias', 'name'));

                    // Loop through all groups, showing as options
                    $results = $tData->fetch_rows($query);
                    foreach ($results as $group) {
                        $selected = in_array($group['alias'], $pageGroups) ? 'selected' : '';
                        echo '<option '.$selected.' value=\''.$group['alias'].'\'>'.$group['name'].'</option>';
                    }
                ?>
                </select>
            </div>
        </div>

        <div class='col-6' id='nav-links' style='display: none;'>
            <input type='hidden' id='navigation' name='navigation' value=''>

            <h2 class='form-header' style='margin-top: 0;'>This layout allows navigation!</h2>
            <div class='form-group'>
                <div id='link-area'>
                    <?php
                    $themeLinks = explode(',', $page['navigation']);

                    $i = 1;
                    foreach ($themeLinks as $linkInfo) {
                        $link = explode('::', $linkInfo);
                        if ($link[0] == '') $link = array('', '');
                        ?>
                        <div class='link_row' id='link_row<?=$i?>'>
                            <div class='form-group'>
                                <input type='text' class='form-control' autocomplete='off' placeholder='Link Text' id='linktext-<?=$i?>' value='<?=$link[0] ?>' />
                                <input type='text' class='form-control' autocomplete='off' placeholder='Link Path' id='linkpath-<?=$i?>' value='<?=$link[1] ?>' />
                            </div>

                            <?php if ($i > 1): ?>
                            <div class='form-control-static'>
                                <a href='#' onclick="return remove_link('<?php echo $i; ?>');">Remove</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $i++;
                    }
                    ?>
                </div>
            </div>

            <hr class='form-split'>

            <div class='form-group'>
                <a href='#' onclick='return add_new_link();'>Add Another</a>
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
</script>