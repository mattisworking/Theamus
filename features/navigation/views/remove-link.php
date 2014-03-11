<?php

$get = filter_input_array(INPUT_GET);

if (isset($get['id'])) {
    $id = $get['id'];
    if (is_numeric($id)) {
        $links_table = $tDataClass->prefix."_links";
        $sql['link'] = "SELECT * FROM `".$links_table."` WHERE `id`='".$id."'";
        $qry['link'] = $tData->query($sql['link']);

        if ($qry['link']) {
            if ($qry['link']->num_rows > 0) {
                $link = $qry['link']->fetch_assoc();
            } else {
                $error[] = "There was an error when finding the link requested.";
            }
        } else {
            $error[] = "There was an issue querying the database.";
        }
    } else {
        $error[] = "The ID provided isn't valid.";
    }
} else {
    $error[] = "There's no link ID defined.";
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
        <input type="button" class="admin-purpbtn" onclick="close_remove_link();" value="Close" />
    </div>
    <?php else: ?>
    <input type="hidden" name="link_id" id="link_id" value="<?=$link['id']?>" />
    Are you sure you want to remove the link <b><?=$link['text']?></b>?<br />
    <span style="color: #AAA; font-size: 9pt; margin: 0 10px;">(<?=$link['path']?>)</span>
    <br/><br/>Removing a link cannot be undone.
    <div class="window-options">
        <input type="button" value="OK" onclick="return submit_remove_link();" class="admin-greenbtn" />
        <input type="button" class="admin-redbtn" onclick="close_remove_link();" value="Cancel" />
    </div>
    <?php endif; ?>
</div>