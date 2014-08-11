<?php

// Define the information from the URL
$get = filter_input_array(INPUT_GET);

// Define the page number
$page = isset($get['page']) ? $get['page'] : 1;

$media_options = ''; // Initialize the media options array
if ($Theamus->User->has_permission('remove_media')) {
    $media_options = implode(array(
        '<div class="media-list-options">',
        '<a href="#" class="remove" data-id="%id%" title="Remove"><span class="glyphicon ion-close"></span></a>',
        '</div>'
    ));
}

// Define the template to show the media with
$template = implode(array(
    '<div class="media-item">',
    $media_options,
    '::"%type%" == "image" ? "<img src=\'media/%path%\' alt=\'\'>" : ""::',
    '::"%type%" == "object" ? "<iframe type=\'application/pdf\' src=\'media/%path%\'></iframe>" : ""::',
    '</div>'
));

// Query the database for all media
$query = $Theamus->DB->select_from_table(
    $Theamus->DB->system_table('media'),
    array('id', 'path', 'type'));

// Check the query for errors
if (!$query) {
    $Theamus->Log->query($Theamus->DB->get_last_error()); // Log the query error

    die($Theamus->notify('danger', 'Failed to get media.'));
}

// Check the query for results
if ($Theamus->DB->count_rows($query) == 0) die($Theamus->notify('info', 'There is no media.'));

// Define the information from the query
$results = $Theamus->DB->fetch_rows($query);
$media = isset($results[0]) ? $results : array($results);

// Define the Pagination information
$Theamus->Pagination->set_page_data(array(
    'data'          => $media,
    'per_page'      => 10,
    'current'       => $page,
    'list_template' => $template
));

// Show the media an pagination links
$Theamus->Pagination->print_list();
$Theamus->Pagination->print_pagination('get_media_list', 'admin-pagination');