<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : "";
$page = isset($get['page']) ? $get['page'] : 1;

$template = implode('', array(
    '<li>',
    '<ul class=\'theme-options\'>',
    $this->tUser->has_permission('edit_themes') ? '::%active% == 0 ? "<li><a href=\'\' data-id=\'%id%\' name=\'activate-theme-link\'>Enable</a></li>" : ""::' : '',
    $this->tUser->has_permission('edit_themes') ? '<li><a href=\'#\' name=\'edit-theme-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $this->tUser->has_permission('remove_themes') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-theme-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
    '</ul>',
    '<span class=\'theme-name\'>%name%</span>',
    '<span class=\'theme-alias\'>%alias%</span>',
    '<span class=\'theme-active\'>::%active% > 0 ? "Enabled" : ""::</span>',
    '</li>'
));

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
            "list_template"     => $template
        ));

        echo '<ul>';
        $tPages->print_list();
        echo '</ul>';
        $tPages->print_pagination('themes_next_page', 'admin-pagination');
    } else {
        notify("admin", "info", "There are no themes to show.");
    }
} else {
    notify("admin", "failure", "There was an issue when querying the database for themes.");
}