<?php

function format_bytes($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, 2)." ".$units[$pow];
}

$get = filter_input_array(INPUT_GET);

$page = 1;
if (isset($get['page'])) $page = $tData->real_escape_string($get['page']);

$template = <<<TEMPLATE
<div class="editor_window-image">
    <span class="image">
        ::'%type%' == "image" ? '<img src="media/images/%path%" alt="">' : ""::
        ::'%type%' == "object" ? '<iframe type="application/pdf" src="media/images/%path%"></iframe>' : ""::
    </span>
    <div class="info-wrapper">
        <div>
            <span class="filename">%file_name%</span>
            <span class="filesize">::format_bytes("%file_size%")::</span>
        </div>
        <div class="options">
            <a href="#" name="add-image" data-type="%type%" data-path="media/images/%path%">Add this image</a>
        </div>
    </div>
</div>
TEMPLATE;

$tPages->set_page_data(array(
    "sql" => "SELECT * FROM `".$tDataClass->prefix."media`",
    "per_page" => 9,
    "current" => $page,
    "list_template" => $template
));
$tPages->print_list();
$tPages->print_pagination("editor.images_next_page");
?>
<script>
    $("#add-image-url").remove();
</script>