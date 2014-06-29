<?php

$alias = urldecode(filter_input(INPUT_GET, "page"));

// Query the database for all of the site's pages
$query = $tData->select_from_table($tData->prefix."pages", array("alias", "title"));

// Check for a valid query
if ($query != false && $tData->count_rows($query) > 0) {
    $results = $tData->fetch_rows($query);
    $pages = isset($results[0]) ? $results : array($results);

    // Loop through the results, printing out the options
    foreach ($pages as $page) {
        $s = $alias == $page['alias'] ? "selected" : "";
        echo "<option value='".$page['alias']."' $s>".$page['title']."</option>";
    }
} else {
    // Throw out an error message in the form of an option
    echo "<option>Error loading pages</option>";
}