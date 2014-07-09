<?php

$get = filter_input_array(INPUT_GET);

if (isset($get['id'])) {
    $id = $get['id'];
    if (is_numeric($id)) {
        $query_page = $tData->select_from_table($tData->prefix.'pages', array('id', 'title', 'alias'), array(
            'operator'  => '',
            'conditions'=> array('id' => $id)
        ));

        if ($query_page != false) {
            if ($tData->count_rows($query_page) > 0) {
                $page = $tData->fetch_rows($query_page);
            } else {
                $error[] = 'There was an error when finding the page requested.';
            }
        } else {
            $error[] = 'There was an issue querying the database.';
        }
    } else {
        $error[] = 'The ID provided isn\'t valid.';
    }
} else {
    $error[] = 'There\'s no page ID defined.';
}
?>

<!-- Pages Tabs -->
<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<!-- Form Results -->
<div id='remove-result'></div>

<?php
if (!empty($error)):
    alert_notify('danger', $error[0]);
else:
?>

    <form class='form' id='remove-group-form' style='width: 500px;'>
        <div class='col-12'>
            <input type='hidden' name='page_id' id='page_id' value='<?=$page['id']?>' />
            Are you sure you want to remove the page <b><?=$page['title']?></b>?
            <br/><br/>Removing a page cannot be undone.
        </div>

        <?php
        // Find associated links
        $query_links = $tData->select_from_table($tData->prefix.'links', array('id'), array(
            'operator'  => '',
            'conditions'=> array('[%]path' => $page['alias'].'%')));

        if ($query_links != false) {
            if ($tData->count_rows($query_links) > 0):
        ?>
        <hr class='form-split'>

        <div class='form-group'>
            <label class='checkbox'>
                <input type='checkbox' name='remove_links' id='remove_links' checked>
                Remove associated links as well?
            </label>
        </div>
        <?php
            else:
                echo '<input type=\'hidden\' id=\'remove_links\' name=\'remove_links\' value=\'false\'>';
            endif;
        }
        ?>

        <hr class='form-split'>

        <div class='form-button-group'>
            <button type='submit' class='btn btn-success'  onclick='return submit_remove_page();'>Remove</button>
        </div>
    </form>
<?php endif; ?>

<script>
    admin_window_run_on_load('change_pages_tab');
</script>