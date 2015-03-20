<?php

$current_page   = isset($Theamus->Call->parameters[0]) ? $Theamus->Call->parameters[0]  : 1;
$media = $Media->get_media_listing($current_page);

if (empty($media[0])):
    $Theamus->notify("info", "There's no media!  Upload something!");
else:
    echo "<div class=\"media_listing-wrapper\">";
    foreach ($media as $item):
?>
    <div class="media_listing-row">
        <span class="media_listing-img">
            <img src="<?php echo "media/{$item['path']}"; ?>" alt="<?php echo $item['file_name']; ?>">
        </span>
        <span class="media_listing-name">
            <?php echo $item['file_name']; ?>
        </span>
        <span class="media_listing-options">
            <a href="#" class="remove"
               data-id="<?php echo $item['id']; ?>">
                Remove</a> |
            <a href="#" name="media_info-link" 
               data-id="<?php echo $item['id']; ?>">
                More Information</a>
        </span>
    </div>
<?php
    endforeach;
    echo "<div>";
endif;
?>

<div class="media_listing-pages-wrapper">
    <?php echo implode($Media->get_page_links($current_page)); ?>
</div>

<script>
    admin_window_run_on_load("mediaPageListeners");
    admin_window_run_on_load("mediaInfoListeners");
</script>