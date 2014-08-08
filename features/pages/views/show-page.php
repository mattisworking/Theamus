<?php

// Query the database for this page
$query = $Theamus->DB->select_from_table(
    $Theamus->DB->system_table('pages'),
    array('groups', 'views', 'parsed_content'),
    array('operator' => '',
        'conditions' => array('alias' => $Theamus->Theme->get_page_variable('page_alias'))));

// Check the query for errors
if (!$query) {
    $Theamus->Log->query($Theamus->DB->get_last_error());

    die($Theamus->notify('danger', 'Failed to find this page.'));
}

// Get the database values
$page = $Theamus->DB->fetch_rows($query);

// Show the page content
echo $page['parsed_content'];

// Update the page view count
$views = $page['views'] + 1;
$Theamus->DB->update_table_row(
    $Theamus->DB->system_table('pages'),
    array('views' => $views),
    array('operator' => '',
        'conditions' => array('alias' => $Theamus->Theme->get_page_variable('page_alias'))));