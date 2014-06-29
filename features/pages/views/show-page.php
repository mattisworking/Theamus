<?php

// Query the database for this page
$query = $tData->select_from_table($tData->prefix."pages", array("groups", "views", "content"), array("operator" => "", "conditions" => array("alias" => $tTheme->get_page_variable("page_alias"))));

// Get the database values
$page = $tData->fetch_rows($query);

// Only allow relevant people
$groups = explode(",", $page['groups']);

foreach ($groups as $group) {
	$ingroup[] = $group == "everyone" ? "true" : "false";
	$ingroup[] = $tUser->in_group($group) ? "true" : "false";
}

if (in_array("true", $ingroup)) {
	echo $page['content'];

	// Update the page view count
	$views = $page['views'] + 1;
    $tData->update_table_row($tData->prefix."pages", array("views" => $views), array("operator" => "", "conditions" => array("alias" => $tTheme->get_page_variable("page_alias"))));
} else {
    echo '<div class="content-header">Hah! Caught you.</div>';
	echo "Here's your fun fact of the day: you don't belong here.";
}