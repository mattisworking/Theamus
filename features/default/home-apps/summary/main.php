<?php

$prefix = $tData->prefix;

$info = array("pages", "users", "features", "links", "groups", "media");
foreach($info as $i) {
    $table = $prefix."".$i;
    $query = $tData->select_from_table($table, array("id"));
    $total[$i] = $tData->count_rows($query);
}

foreach ($total as $key => $val):
?>
<div style="border:1px solid #EEE; padding: 5px 10px; margin: 5px 0 0;">
    <span style="font-size:14pt; color: #AAA;"><?=$val?></span>
    <span style="padding: 0 5px;"><?=ucfirst($key)?></span>
</div>
<?php endforeach; ?>