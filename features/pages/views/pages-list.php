<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : "";
$page = isset($get['page']) ? $get['page'] : 1;


$template_header = <<<TEMPLATE
        <ul class="header">
            <li style="width: 300px;">Title</li>
            <li style="width: 200px;">Views</li>
        </ul>
TEMPLATE;

$template = <<<TEMPLATE
<ul>
    <li class="admin-listoptions">
        ::\$tUser->has_permission("edit_pages") ? "<a href='#' onclick=\"return admin_go('pages', 'pages/edit&id=%id%');\">Edit</a>" : ""::
        ::\$tUser->has_permission("remove_pages") && %permanent% == 0 ? "<a href='#' onclick=\"return remove_page('%id%');\">Remove</a>" : ""::
    </li>
    <li style="width: 300px;">::strlen("%title%") > 40 ? substr("%title%", 0, 40)."..." : "%title%"::</li>
    <li style="width: 200px;">%views%</li>
</ul>
TEMPLATE;

$query = $tData->select_from_table($tData->prefix."pages", array("id", "title", "views", "permanent"), array(
    "operator"  => "",
    "conditions"=> array("[%]title" => $search."%")
));

if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        $pages = isset($results[0]) ? $results : array($results);

        $tPages->set_page_data(array(
            "data"              => $pages,
            "per_page"          => 25,
            "current"           => $page,
            "list_template"     => $template,
            "template_header"   => $template_header
        ));
        $tPages->print_list();
        $tPages->print_pagination();
    } else {
        notify("admin", "info", "There are no pages to show.");
    }
} else {
    notify("admin", "failure", "There was an error when querying for pages.");
}