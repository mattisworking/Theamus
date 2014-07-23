<?php

$get = filter_input_array(INPUT_GET);

if (isset($get['id'])) {
    $id = $get['id'];
    if (is_numeric($id)) {
        $query = $tData->select_from_table($tData->prefix."links", array("id", "text", "path"), array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        if ($query != false) {
            if ($tData->count_rows($query) > 0) {
                $link = $tData->fetch_rows($query);
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

<!-- Navigation form result -->
<div id='navigation-result' style='margin-top: 15px;'></div>

<!-- Form Results -->
<div id='remove-result'></div>

<?php
if (!empty($error)):
    alert_notify('danger', $error[0]);
else:
?>
<form class='form-horizontal' id='remove-link-form' style='width: 500px;'>
    <div class='col-12'>
        <input type="hidden" name="link_id" id="link_id" value="<?=$link['id']?>">
        Are you sure you want to remove the link <b><?=$link['text']?></b>?<br>
        <span style="color: #AAA; font-size: 9pt; margin: 0 10px;">(<?=$link['path']?>)</span><br><br>
        Removing a link cannot be undone.
    </div>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success' onclick="return submit_remove_link();">Remove</button>
    </div>
</form>
<?php endif; ?>