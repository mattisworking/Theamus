<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : "";
$page = isset($get['page']) ? $get['page'] : 1;

$template_header = <<<TEMPLATE
        <ul class="header">
            <li style="width: 175px;">Text</li>
            <li class="path" >Path</li>
            <li class="affiliated-groups" style="width: 300px;">Affiliated Groups</li>
        </ul>
TEMPLATE;

$template = <<<TEMPLATE
<ul>
    <li class="admin-listoptions">
        ::\$tUser->has_permission("edit_links") ? "<a href='#' onclick=\"return admin_go('pages', 'navigation/edit&id=%id%');\">Edit</a>" : ""::
        ::\$tUser->has_permission("remove_links") ? "<a href='#' onclick=\"return remove_link('%id%');\">Remove</a>" : ""::
    </li>
    <li style="width: 175px;">
        ::strlen("%text%") >= 20 ? substr("%text%", 0, 20)."..." : "%text%"::
    </li>
    <li class="path">%path%</li>
    <li class="affiliated-groups" style="width: 300px;" title="::ucwords(str_replace(',', ', ', str_replace('_', ' ', '%groups%')))::">::count(explode(",", "%groups%"))::</li>
</ul>
TEMPLATE;

$query = $tData->select_from_table($tData->prefix."links", array("id", "text", "path", "groups"), array(
    "operator"  => "OR",
    "conditions"=> array(
        "[%]text" => $search."%",
        "[%]path" => $search."%"
    )
));

if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        $links = isset($results[0]) ? $results : array($results);

        $tPages->set_page_data(array(
            "data"              => $links,
            "per_page"          => 25,
            "current"           => $page,
            "template_header"   => $template_header,
            "list_template"     => $template
        ));

        $tPages->print_list();
        $tPages->print_pagination();
    } else {
        notify("admin", "info", "There are no links to show.");
    }
} else {
    notify("admin", "failure", "There was an error querying the database for links");
}