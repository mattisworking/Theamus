<div class="admin_content-header">
    <span class="admin_content-header-img">
        <img id="admin_content-header-img" src="features/navigation/img/create-nav.png" alt="" />
    </span>
    <div class="right" style="margin-top: -2px;">
        <input type="button" name="cancel" value="Go Back" />
    </div>
	<div class="admin_content-header-text">Create a New Link</div>
</div>

<form id="info-form" style="visiblity: hidden; position: absolute;">
    <input type="hidden" name="page-type" value="create" />
    <?php
    $cols = array("id", "alias", "text", "path", "weight", "groups", "type", "position", "child_of");
    $vals = array("", "", "", "", "1", "", "url", "main", "0");
    for ($i=0; $i<count($cols); $i++):
    ?>
    <input type="hidden" name="<?=$cols[$i]?>" value="<?=$vals[$i]?>" />
    <?php endfor; ?>
</form>
<div class="admin_page-content" id="form-wrapper"></div>