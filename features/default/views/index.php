<?php

$HomePage = new HomePage($Theamus);
$i = $HomePage->redirect();

$query = $Theamus->DB->select_from_table($Theamus->DB->system_table("pages"), array("views"), array("operator" => "", "conditions" => array("alias" => $i['alias'])));
$row = $Theamus->DB->fetch_rows($query);

$views = $row['views'] + 1;
$Theamus->DB->update_table_row($Theamus->DB->system_table("pages"), array("views" => $views), array("operator" => "", "conditions" => array("alias" => $i['alias'])));

echo $HomePage->page_content;