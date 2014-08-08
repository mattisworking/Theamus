<?php

// Define the search query
$search = filter_input(INPUT_GET, 'search') != '' ? filter_input(INPUT_GET, 'search') : '';

// Define the page number
$page = filter_input(INPUT_GET, 'page') != '' ? filter_input(INPUT_GET, 'page') : 1;

// Define the template to show the list with
$template = implode('', array(
    '<li>',
    '<ul class=\'page-options\'>',
    $Theamus->User->has_permission('edit_pages') ? '<li><a href=\'#\' name=\'edit-page-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $Theamus->User->has_permission('remove_pages') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-page-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
    '</ul>',
    '<span class=\'page-title\'>%title%</span>',
    '<span class=\'page-views\'>%views% views</span>',
    '</li>'
));

// Query the database for results
$query = $Theamus->DB->select_from_table(
    $Theamus->DB->system_table('pages'),
    array('id', 'title', 'views', 'permanent'),
    array('operator' => '',
        'conditions' => array('[%]title' => $search.'%')));

// Check the query for errors
if (!$query) {
    $Theamus->Log->query($Theamus->DB->get_last_error()); // Log the query error

    die($Theamus->notify('danger', 'Failed to find pages.'));
}

// Check the query for results
if ($Theamus->DB->count_rows($query) == 0) die($Theamus->notify('info', 'No pages were found.'));

// Define the query information
$results = $Theamus->DB->fetch_rows($query);
$pages = isset($results[0]) ? $results : array($results);

// Setup the pagination information
$Theamus->Pagination->set_page_data(array(
    'data'          => $pages,
    'per_page'      => 15,
    'current'       => $page,
    'list_template' => $template));

// Show the list and the page numbers
echo '<ul>';
$Theamus->Pagination->print_list();
echo '</ul>';
$Theamus->Pagination->print_pagination('get_pages', 'admin-pagination');