<?php

// Define the information from the URL
$get = filter_input_array(INPUT_GET);

// Define the search query
$search = isset($get['search']) ? $get['search'] : '';

// Define the page number
$page = isset($get['page']) ? $get['page'] : 1;

// Define the template for the navigation list
$template = implode('', array(
    '<li>',
    '<ul class="navigation-options">',
    $Theamus->User->has_permission('edit_links') ? '<li><a href="#" name="edit-navigation-link" data-id="%id%"><span class="glyphicon ion-edit"></span></a></li>' : '',
    $Theamus->User->has_permission('remove_links') ? '<li><a href="#" name="remove-navigation-link" data-id="%id%"><span class="glyphicon ion-close"></span></a></li>' : '',
    '</ul>',
    '<span class="link-text">%text%</span>',
    '<span class="link-path">%path%</span>',
    '</li>'
));

// Query the database for links
$query = $Theamus->DB->select_from_table(
        $Theamus->DB->system_table('links'),
        array('id', 'text', 'path', 'groups'),
        array('operator' => 'OR',
            'conditions' => array(
                '[%]text' => $search.'%',
                '[%]path' => $search.'%')));

// Check the query for errors
if (!$query) {
    $Theamus->Log->query($Theamus->DB->get_last_error()); // Log the query error
    
    die($Theamus->notify('danger', 'Failed to get the links.'));
}

// Check the query for results
if ($Theamus->DB->count_rows($query) == 0) die($Theamus->notify('info', 'No links found.'));

// Get the link information from the database
$results = $Theamus->DB->fetch_rows($query);
$links = isset($results[0]) ? $results : array($results);

// Define the pagination data
$Theamus->Pagination->set_page_data(array(
    'data'          => $links,
    'per_page'      => 15,
    'current'       => $page,
    'list_template' => $template));

// Show the list of links and the page numbers associated to them
echo '<ul>';
$Theamus->Pagination->print_list();
echo '</ul>';
$Theamus->Pagination->print_pagination('get_navigation_links', 'admin-pagination');
