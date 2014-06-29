<?php

$get = filter_input_array(INPUT_GET);

if (isset($get['id'])) {
    $id = $get['id'];
    if (is_numeric($id)) {
        $query_page = $tData->select_from_table($tData->prefix."pages", array("id", "title", "alias"), array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        if ($query_page != false) {
            if ($tData->count_rows($query_page) > 0) {
                $page = $tData->fetch_rows($query_page);
            } else {
                $error[] = "There was an error when finding the page requested.";
            }
        } else {
            $error[] = "There was an issue querying the database.";
        }
    } else {
        $error[] = "The ID provided isn't valid.";
    }
} else {
    $error[] = "There's no page ID defined.";
}

?>
<div class="window-header">
    <?php if (empty($error)): ?>
    Are you sure?
    <?php else: ?>
    Hmmm...
    <?php endif; ?>
</div>
<div class="window-content">
    <?php
    if (!empty($error)):
        notify("admin", "failure", $error[0]);
    ?>
    <div class="window-options">
        <input type="button" class="admin-purpbtn" onclick="close_remove_page();" value="Close" />
    </div>
    <?php else: ?>
    <input type="hidden" name="page_id" id="page_id" value="<?=$page['id']?>" />
    Are you sure you want to remove the page <b><?=$page['title']?></b>?
    <br/><br/>Removing a page cannot be undone.
    <?php
    // Find associated links
    $query_links = $tData->select_from_table($tData->prefix."links", array("id"), array(
        "operator"  => "",
        "conditions"=> array("[%]path" => $page['alias']."%")
    ));

    if ($query_links != false) {
        if ($tData->count_rows($query_links) > 0):
    ?>
    <h2>More options</h2>
    <div class="admin-cboxwrapper">
        <input type="checkbox" class="admin-switchcbox" name="remove_links"
            id="remove_links" checked>
        <label class="admin-switchlabel yn" for="remove_links">
          <span class="admin-switchinner"></span>
          <span class="admin-switchswitch"></span>
        </label>
    </div>
    <span style="padding: 4px 10px; display:inline-block;">
        Would you like to remove any associated links?
    </span>
    <div class="clearfix"></div>
    <?php
        else:
            echo "<input type='hidden' id='remove_links' name='remove_links' value='false' />";
        endif;
    }
    ?>
    <div class="window-options">
        <input type="button" value="OK" onclick="return submit_remove_page();" class="admin-greenbtn" />
        <input type="button" class="admin-redbtn" onclick="close_remove_page();" value="Cancel" />
    </div>
    <?php endif; ?>
</div>