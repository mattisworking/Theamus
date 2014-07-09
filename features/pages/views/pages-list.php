<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : "";
$page = isset($get['page']) ? $get['page'] : 1;

$template = implode('', array(
    '<li>',
    '<ul class=\'page-options\'>',
    $this->tUser->has_permission('edit_pages') ? '<li><a href=\'#\' name=\'edit-page-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $this->tUser->has_permission('remove_pages') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-page-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
    '</ul>',
    '<span class=\'page-title\'>%title%</span>',
    '<span class=\'page-views\'>%views% views</span>',
    '</li>'
));


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
            "list_template"     => $template
        ));

        echo '<ul>';
        $tPages->print_list();
        echo '</ul>';
        $tPages->print_pagination('groups_next_page', 'admin-pagination');
    } else {
        alert_notify("info", "There are no pages to show.");
    }
} else {
    alert_notify("danger", "There was an error when querying for pages.");
}