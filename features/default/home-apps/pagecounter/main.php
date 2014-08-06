<?php

// Query the database for pages
$query = $Theamus->DB->select_from_table(
    $Theamus->DB->system_table('pages'),
    array('title', 'views'),
    array(),
    'ORDER BY `views` DESC LIMIT 5');

// Chec the query for errors
if ($query != false) {
    // Check the amount of rows returned
    if ($Theamus->DB->count_rows($query) > 0) {
        // Define the pages information from the database
        $results = $Theamus->DB->fetch_rows($query);
        $pages = isset($results[0]) ? $results : array($results);

        $all_pages = array(); // Initialize the chart data information array

        // Loop through all of the pages information, adding it to the chart data array
        foreach ($pages as $page) $all_pages[$page['title']] = $page['views'];

        // Encode the chart data array
        $chart_data = json_encode($all_pages);
?>

        <canvas id='page_canvas' width='400' height='250' style='margin:0 auto; display:block;'></canvas>
        <input type='hidden' id='all-pages' value='<?php echo $chart_data; ?>'>
        
<?php
    } else $Theamus->notify('info', 'There are no pages to show!');
} else $Theamus->notify('failure', 'There was an issue querying the database.');
?>