<?php

// Define the information from the URL
$get = filter_input_array(INPUT_GET);

// Define the search query
$search = isset($get['search']) ? $get['search'] : '';

// Define the page number
$page = isset($get['page']) ? $get['page'] : 1;

// Define the template for the groups list
$template = implode('', array(
    '<li>',
    '<ul class=\'group-options\'>',
    $Theamus->User->has_permission('edit_groups') ? '<li><a href=\'#\' name=\'edit-group-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $Theamus->User->has_permission('remove_groups') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-group-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
    '</ul>',
    '<span class=\'group-alias\'>%name%</span>',
    '</li>'
));

// Query the groups database for groups
$query = $Theamus->DB->select_from_table(
        $Theamus->DB->system_table('groups'),
        array('id', 'alias', 'name', 'permanent'),
        array('operator'  => 'OR',
             'conditions' => array(
                '[%]alias' => $search.'%',
                '[%]name'  => $search.'%')));

// Check the query for NO errors
if ($query != false) {
    // Check the query for results
    if ($Theamus->DB->count_rows($query) > 0) {
        // Define the results
        $results = $Theamus->DB->fetch_rows($query);
        $groups = isset($results[0]) ? $results : array($results);
        
        $editable_groups = array(); // Initialize the editable array

        // Loop through the groups to find out what can be eidted
        foreach ($groups as $group) {
            // Check the user's permissions to see if they can edit this group or not
            if ($Theamus->User->in_group($group['alias']) || ($Theamus->User->is_admin() && $Theamus->User->in_group('administrators'))) {
                $editable_groups[] = $group;
            }
        }

        // Define the pagination data
        $Theamus->Pagination->set_page_data(array(
            'data'              => $editable_groups,
            'per_page'          => 15,
            'current'           => $page,
            'list_template'     => $template));

        // Print the list out
        echo '<ul>';
        $Theamus->Pagination->print_list();
        echo '</ul>';
        
        // Print the page number links out
        $Theamus->Pagination->print_pagination('groups_next_page', 'admin-pagination');
        
    // Notify the user there aren't any groups (this should be impossible
    } else $Theamus->notify('info', 'There are no groups to show.');
} else {
    $Theamus->Log->query($Theamus->DB->get_last_error()); // Log the query error in the database
    $Theamus->notify('danger', 'Error finding groups.'); // Let the user know something done screwed up
}