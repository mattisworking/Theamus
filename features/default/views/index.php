<?php

// Define the homepage information
$HomePage = new HomePage($Theamus);
$i = $HomePage->redirect(); // Redirect if necessary

// Query the database for a page
$query = $Theamus->DB->select_from_table($Theamus->DB->system_table('pages'), array('views'), array('operator' => '', 'conditions' => array('alias' => $i['alias'])));
$row = $Theamus->DB->fetch_rows($query); // Define the page information

// Add to the page view count
$views = $row['views'] + 1;
$Theamus->DB->update_table_row($Theamus->DB->system_table('pages'), array('views' => $views), array('operator' => '', 'conditions' => array('alias' => $i['alias'])));

//Show the page information
echo $HomePage->page_content;