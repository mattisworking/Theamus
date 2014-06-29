<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : "";
$page = isset($get['page']) ? $get['page'] : 1;

$template_header = <<<TEMPLATE
        <ul class="header">
            <li style="width: 200px;">Name</li>
            <li style="width: 200px;">Folder</li>
            <li style="width: 100px;">Active</li>
        </ul>
TEMPLATE;

$template = <<<TEMPLATE
<ul>
    <li style="width: 200px;">::strlen("%name%") > 25 ? substr("%name%", 0, 25)."..." : "%name%"::<li>
    <li style="width: 200px;">%alias%<li>
    <li style="width: 100px;">::%active% > 0 ? "Yes" : "No"::</li>
    <li class="admin-listoptions">
        ::\$tUser->has_permission("edit_themes") ? "<a href='#' onclick=\"return admin_go('settings', 'appearance/edit&id=%id%');\">Edit</a>" : ""::
        ::\$tUser->has_permission("edit_themes") && %active% != 1 ? "<a href='#' name='make-active' data-id='%id%'>Make Active</a>" : ""::
        ::\$tUser->has_permission("remove_themes") && %permanent% == 0 ? "<a href='#' onclick=\"return remove_theme('%id%');\">Remove</a>" : ""::
    </li>
</ul>
TEMPLATE;

$query = $tData->select_from_table($tData->prefix."themes", array("name", "id", "permanent", "active", "alias"), array(
    "operator"  => "OR",
    "conditions"=> array(
        "[%]alias"  => $search."%",
        "[%]name"   => $search."%"
    )
));

if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        $themes = isset($results[0]) ? $results : array($results);

        $tPages->set_page_data(array(
            "data"              => $themes,
            "per_page"          => 25,
            "current"           => $page,
            "template_header"   => $template_header,
            "list_template"     => $template
        ));

        $tPages->print_list();
        $tPages->print_pagination();
    } else {
        notify("admin", "info", "There are no themes to show.");
    }
} else {
    notify("admin", "failure", "There was an issue when querying the database for themes.");
}