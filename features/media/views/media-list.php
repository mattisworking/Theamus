<?php

$get = filter_input_array(INPUT_GET);

$page = 1;
if (isset($get['page'])) {
    $page = $get['page'];
}

$template = <<<TEMPLATE
    <div class="media_list-img">
        <div class="media_list-options">
            <a href="#" onclick="return remove_media('%id%');" class="media_list-remove"
                title="Remove">X</a>
        </div>
        ::'%type%' == "image" ? '<img src="media/images/%path%" alt="">' : ""::
        ::'%type%' == "object" ? '<iframe type="application/pdf" src="media/images/%path%"></iframe>' : ""::
    </div>
TEMPLATE;

$query = $tData->select_from_table($tData->prefix."media", array("id", "path", "type"));
if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        $media = isset($results[0]) ? $results : array($results);

        $tPages->set_page_data(array(
            "data"              => $media,
            "per_page"          => 10,
            "current"           => $page,
            "list_template"     => $template
        ));

        $tPages->print_list();
        echo "<div class='clearfix'></div>";
        $tPages->print_pagination("next_page");
    } else {
        notify("admin", "info", "There is no media to display.");
    }
} else {
    notify("admin", "failure", "There was an error querying the database for media.");
}