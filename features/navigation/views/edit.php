<?php

$error = false;

// Define and clean the GET request
$get = filter_input_array(INPUT_GET);

// Get the link ID
if (isset($get['id']) && $get['id'] != "") {
    $id = $get['id'];
} else {
    $error = "I can't find the link you're looking for.";
}

$query = $tData->select_from_table($tData->prefix."links", array(), array(
    "operator"  => "",
    "conditions"=> array("id" => $id)
));

// Check for a valid query
if ($query != false && $tData->count_rows($query) > 0) {
    $link = $tData->fetch_rows($query);
} else {
    $error = "This link does not exist in the database.";
}

?>

<div class="admin_content-header">
    <span class="admin_content-header-img">
        <img id="admin_content-header-img" src="features/navigation/img/edit-nav.png" alt="" />
    </span>
    <div class="right" style="margin-top: -2px;">
        <input type="button" name="cancel" value="Go Back" />
    </div>
	<div class="admin_content-header-text">
        <?php
        if ($error != false):
            echo "Error";
        else:
        ?>
        Edit the link "<?=$link['text']?>"
        <?php endif; ?>
    </div>
</div>

<?php if ($error != false):
    echo "<div class='admin_page-content'>";
    notify("admin", "failure", $error);
    echo "</div>";
else: ?>
<form id="info-form" style="visiblity: hidden; position: absolute;">
    <input type="hidden" name="page-type" value="save" />
    <?php
    $cols = array("id", "alias", "text", "path", "weight", "groups", "type", "child_of");
    foreach ($cols as $item):
    ?>
    <input type="hidden" name="<?=$item?>" value="<?=htmlspecialchars($link[$item])?>" />
    <?php endforeach; ?>
    <input type="hidden" name="position" value="<?=htmlspecialchars($link["location"])?>" />
</form>
<div class="admin_page-content" id="form-wrapper"></div>
<?php endif; ?>