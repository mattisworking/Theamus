<?php

$HomePage = new HomePage();
$i = $HomePage->redirect();

$query = $tData->select_from_table($tData->prefix."pages", array("views"), array("operator" => "", "conditions" => array("alias" => $i['alias'])));
$row = $tData->fetch_rows($query);

$views = $row['views'] + 1;
$tData->update_table_row($tData->prefix."pages", array("views" => $views), array("operator" => "", "conditions" => array("alias" => $i['alias'])));

echo $HomePage->page_content;