<?php

$media_id = isset($Theamus->Call->parameters[0]) ? $Theamus->Call->parameters[0] : 0;

if ($media_id == 0):
    $Theamus->notify("info", "Uh oh, this media item couldn't be found!");
else:
    $media = $Media->get_media($media_id);
?>

<form class="form" onsubmit="return false;">
    <div class="media_info-img">
        <img src="<?php echo "media/{$media['path']}"; ?>" alt="">
    </div>

    <hr class="form-split">

    <div class="form-group">
        <label class="control-label">Media Path</label>
        <input type="text" class="form-control" value="<?php echo "media/{$media['path']}"; ?>">
    </div>

    <div class="form-group">
        <label class="control-label">Item Name</label>
        <p class="form-control-static"><?php echo $media['file_name']; ?></p>
    </div>

    <div class="form-group">
        <label class="control-label">Item Size</label>
        <p class="form-control-static"><?php echo "{$media['file_size']}B"; ?></p>
    </div>
</form>
<?php
endif;