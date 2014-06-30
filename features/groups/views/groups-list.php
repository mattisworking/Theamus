<?php

$get = filter_input_array(INPUT_GET);

$search = '';
if (isset($get['search'])) {
    $search = $get['search'];
}

$page = 1;
if (isset($get['page'])) {
    $page = $get['page'];
}


$template = implode('', array(
    '<li>',
    '<ul class=\'group-options\'>',
    $this->tUser->has_permission('edit_groups') ? '<li><a href=\'#\' name=\'edit-group-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $this->tUser->has_permission('remove_groups') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-group-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
    '</ul>',
    '<span class=\'group-name\'>%alias%</span>',
    '<span class=\'group-alias\'>%name%</span>',
    '</li>'
));

$query_data = array(
    'table_name'    => $tData->prefix.'groups',
    'data'          => array('id', 'alias', 'name', 'permanent'),
    'clause'        => array(
        'operator'  => 'OR',
        'conditions'=> array(
            '[%]alias' => $search.'%',
            '[%]name'  => $search.'%'
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
            if ($tUser->in_group($group['alias']) || ($tUser->is_admin() && $tUser->in_group('administrators'))) {
                $editable_groups[] = $group;
            }
        }

        $tPages->set_page_data(array(
            'data'              => $groups,
            'per_page'          => 25,
            'current'           => $page,
            'list_template'     => $template
        ));

        echo '<ul>';
        $tPages->print_list();
        echo '</ul>';
        $tPages->print_pagination('groups_next_page', 'admin-pagination');
    } else {
        alert_notify('info', 'There are no groups to show.');
    }
} else {
    alert_notify('danger', 'There was an error querying the database for groups.');
}