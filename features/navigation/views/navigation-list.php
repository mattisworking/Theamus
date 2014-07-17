<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : "";
$page = isset($get['page']) ? $get['page'] : 1;

$template = implode('', array(
    '<li>',
    '<ul class=\'navigation-options\'>',
    $this->tUser->has_permission('edit_links') ? '<li><a href=\'#\' name=\'edit-navigation-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $this->tUser->has_permission('remove_links') ? '<li><a href=\'#\' name=\'remove-navigation-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>' : '',
    '</ul>',
    '<span class=\'link-text\'>%text%</span>',
    '<span class=\'link-path\'>%path%</span>',
    '</li>'
));

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
            "list_template"     => $template
        ));

        echo '<ul>';
        $tPages->print_list();
        echo '</ul>';
        $tPages->print_pagination('next_page', 'admin-pagination');
    } else {
        notify("admin", "info", "There are no links to show.");
    }
} else {
    notify("admin", "failure", "There was an error querying the database for links");
}