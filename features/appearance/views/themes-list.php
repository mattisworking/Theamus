<?php

// Define the information from the URL
$get = filter_input_array(INPUT_GET);

// Define the SEARCH QUERY
$search = isset($get['search']) ? $get['search'] : '';

// Define the PAGE NUMBER
$page = isset($get['page']) ? $get['page'] : 1;

// Define the TEMPLATE to show the data with
$template = implode('', array(
    '<li>',
    '<ul class=\'theme-options\'>',
    $Theamus->User->has_permission('edit_themes') ? '::%active% == 0 ? "<li><a href=\'\' data-id=\'%id%\' name=\'activate-theme-link\'>Enable</a></li>" : ""::' : '',
    $Theamus->User->has_permission('edit_themes') ? '<li><a href=\'#\' name=\'edit-theme-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $Theamus->User->has_permission('remove_themes') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-theme-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
    '</ul>',
    '<span class=\'theme-name\'>%name%</span>',
    '<span class=\'theme-alias\'>%alias%</span>',
    '<span class=\'theme-active\'>::%active% > 0 ? "Enabled" : ""::</span>',
    '</li>'));

// QUERY the database for themes
$query = $Theamus->DB->select_from_table(
    $Theamus->DB->system_table('themes'),
    array('id', 'name', 'permanent', 'active', 'alias'), array(
    'operator'  => 'OR',
    'conditions'=> array(
        '[%]alias'  => $search.'%',
        '[%]name'   => $search.'%')));

// Check the QUERY for ERRORS
if (!$query) {
    $Theamus->Log->query($Theamus->DB->get_last_error());
    die($Theamus->notify('danger', 'Failed to get themes.'));
}

// Check the QUERY for RESULTS
if ($Theamus->DB->count_rows($query) == 0) die($Theamus->notify('info', 'No themes were found.'));

// Define the QUERY INFORMATION
$results = $Theamus->DB->fetch_rows($query);
$themes = isset($results[0]) ? $results : array($results);

// Define the PAGINATION INFORMATION
$Theamus->Pagination->set_page_data(array(
    'data'          => $themes,
    'per_page'      => 15,
    'current'       => $page,
    'list_template' => $template));

// PRINT INFORMATION/PAGINATION
echo '<ul>';
$Theamus->Pagination->print_list();
echo '</ul>';
$Theamus->Pagination->print_pagination('get_themes', 'admin-pagination');
