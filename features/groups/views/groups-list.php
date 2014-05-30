<?php

$get = filter_input_array(INPUT_GET);

$search = "";
if (isset($get['search'])) {
    $search = $get['search'];
}

$page = 1;
if (isset($get['page'])) {
    $page = $get['page'];
}


$template_header = <<<TEMPLATE
        <ul class="header">
            <li style="width: 150px;">Group Alias</li>
            <li style="width: 200px;">Group Name</li>
        </ul>
TEMPLATE;
$template = <<<TEMPLATE
<ul>
    <li style="width: 150px;">%alias%<li>
    <li style="width: 200px;">%name%<li>
    <li class="admin-listoptions">
        ::\$tUser->has_permission("edit_groups") ? "<a href='#' onclick=\"return admin_go('accounts', 'groups/edit&id=%id%');\">Edit</a>" : ""::
        ::\$tUser->has_permission("remove_groups") && %permanent% == 0 ? "<a href='#' onclick=\"return remove_group('%id%');\">Remove</a>" : ""::
    </li>
</ul>
TEMPLATE;

$query_data = array(
    "table_name"    => $tData->prefix."_groups",
    "data"          => array("id", "alias", "name", "permanent"),
    "clause"        => array(
        "operator"  => "OR",
        "conditions"=> array(
            "[%]alias" => $search."%",
            "[%]name"  => $search."%"
        )
    )
);
$query = $tData->select_from_table($query_data['table_name'], $query_data['data'], $query_data['clause']);

if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        $groups = isset($results[0]) ? $results : array($results);
        $editable_groups = array();

        foreach ($groups as $group) {
            if ($tUser->in_group($group['alias']) || ($tUser->is_admin() && $tUser->in_group("administrators"))) {
                $editable_groups[] = $group;
            }
        }

        $tPages->set_page_data(array(
            "data"              => $groups,
            "per_page"          => 25,
            "current"           => $page,
            "list_template"     => $template,
            "template_header"   => $template_header
        ));

        $tPages->print_list();
        $tPages->print_pagination("groups_next_page");
    } else {
        notify("admin", "info", "There are no groups to show.");
    }
} else {
    notify("admin", "failure", "There was an error querying the database for groups.");
}