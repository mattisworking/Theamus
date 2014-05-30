<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : "";
$page = isset($get['page']) ? $get['page'] : 1;

$template_header = <<<TEMPLATE
        <ul class="header">
            <li style="width: 175px;">Name</li>
            <li style="text-align:center; width: 200px;">Enabled</li>
            <li style="text-align:center; width: 200px;">Version</li>
        </ul>
TEMPLATE;

$template = <<<TEMPLATE
<ul>
    <li class="admin-listoptions">
        ::\$tUser->has_permission("edit_features") ? "<a href='#' onclick=\"return admin_go('features', 'features/edit&id=%id%');\">Edit</a>" : ""::
        ::\$tUser->has_permission("remove_features") && %permanent% == 0 ? "<a href='#' onclick=\"return remove_feature('%id%');\">Remove</a>" : ""::
    </li>
    <li style="width: 175px;">
        ::strlen("%name%") >= 20 ? substr("%name%", 0, 20)."..." : "%name%"::
    </li>
    <li style="text-align:center; width: 200px;">::%enabled% == 1 ? "Yes" : "No"::</li>
    <li style="text-align:center; width: 200px;">%version%</li>
</ul>
TEMPLATE;

$query = $tData->select_from_table($tData->prefix."_features", array("id", "alias", "name", "permanent", "enabled"), array(
    "operator"  => "",
    "conditions"=> array("[%]name" => $search."%")
), "ORDER BY `name` ASC");

if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        $all_features = isset($results[0]) ? $results : array($results);
        $features = array();

        foreach ($all_features as $item) {
            $feature = array();
            $config_file = path(ROOT."/features/".$item['alias']."/config.php");
            $version = "Unknown";

            if (file_exists($config_file)) {
                include_once $config_file;
                if (isset($feature['version'])) {
                    $version = $feature['version'];
                }
            }

            $item['version'] = $version;
            $features[] = $item;
        }

        $tPages->set_page_data(array(
            "data"              => $features,
            "per_page"          => 25,
            "current"           => $page,
            "template_header"   => $template_header,
            "list_template"     => $template
        ));

        $tPages->print_list();
        $tPages->print_pagination();
    } else {
        notify("admin", "info", "There are no features to show.");
    }
} else {
    notify("admin", "failure", "There was an error querying the database for features.");
}