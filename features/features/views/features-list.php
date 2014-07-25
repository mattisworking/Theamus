<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : "";
$page = isset($get['page']) ? $get['page'] : 1;

$template = implode('', array(
    '<li>',
    '<ul class=\'feature-options\'>',
    $this->tUser->has_permission('edit_features') ? '<li><a href=\'#\' name=\'edit-feature-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $this->tUser->has_permission('remove_features') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-feature-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
    '</ul>',
    '<span class=\'feature-name\'>%name%</span>',
    '<span class=\'feature-enabled\'>::"%enabled%" == 1 ? "Enabled" : "Disabled";::</span>',
    '<span class=\'feature-version\'>%version%</span>',
    '</li>'
));

$query = $tData->select_from_table($tData->prefix."features", array("id", "alias", "name", "permanent", "enabled"), array(
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
                    $version = 'v'.$feature['version'];
                }
            }

            $item['version'] = $version;
            $features[] = $item;
        }

        $tPages->set_page_data(array(
            "data"              => $features,
            "per_page"          => 25,
            "current"           => $page,
            "list_template"     => $template
        ));

        echo '<ul>';
        $tPages->print_list();
        echo '</ul>';

        $tPages->print_pagination('features_next_page', 'admin-pagination');
    } else {
        alert_notify("info", "There are no features to show.");
    }
} else {
    alert_notify("danger", "There was an error querying the database for features.");
}