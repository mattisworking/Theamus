<?php

try {
    $Settings->prelim_update();
} catch (Exception $ex) {
    $Settings->abort_update($ex);
}

?>

<input type="hidden" name="filename" value="<?=$Settings->update_information['filename']?>" />

<div class='form-group'>
    <label class='control-label col-3'>Database Changes</label>
    <div class='col-9'><?=$Settings->update_information['database_changes']?></div>
</div>

<div class='form-group'>
    <label class='control-label col-3'>File Changes</label>
    <div class='col-9'><?=$Settings->update_information['file_changes']?></div>
</div>

<hr class='form-split'>

<div class='form-group'>
    <label class='control-label col-3'>Bug Fixes</label>
    <div class='col-9'>
        <ul style="margin: 0; padding: 0;">
            <?php foreach ($Settings->update_information['bugs'] as $bug): ?>
            <li><?=$bug?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>