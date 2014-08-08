<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<?php

// Define the page ID
$id = filter_input(INPUT_GET, 'id');

// Try to get the page information
try { $page = $Pages->get_page($id); }
catch (Exception $ex) { die($Theamus->notify('danger', $ex->getMessage())); }

?>

<div id='remove-result' style='margin-top: 15px;'></div>

<form class='form' id='remove-page-form' style='width: 500px;'>
    <div class='col-12'>
        <input type='hidden' name='id' id='id' value='<?php echo $page['id']; ?>' />
        Are you sure you want to remove the page <b><?php echo $page['title']; ?></b>?
        <br/><br/>Removing a page cannot be undone.
    </div>

    <?php
    // Query the database for associated links
    $query = $Theamus->DB->select_from_table(
        $Theamus->DB->system_table('links'),
        array('id'),
        array('operator' => '',
            'conditions' => array('[%]path' => $page['alias'].'%')));

    // Check the query for errors
    if (!$query) {
        $Theamus->Log->query($Theamus->DB->get_last_error()); // Log query error

        die($Theamus->notify('danger', 'Failed to get associated links'));
    }

    // Check for query results
    if ($Theamus->DB->count_rows($query) == 0): echo '<input type="hidden" id="remove_links" name="remove_links" value="false">';
    else:
    ?>

    <hr class='form-split'>

    <div class='form-group'>
        <label class='checkbox'>
            <input type='checkbox' name='remove_links' id='remove_links' checked>
            Remove associated links as well?
            <span style='font-weight:normal'>(<?php echo $Theamus->DB->count_rows($query); ?>)</span>
        </label>
    </div>

        <?php
    endif;
    ?>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'  onclick='return submit_remove_page();'>Remove</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_pages_tab');
    admin_window_run_on_load('remove_page');
</script>