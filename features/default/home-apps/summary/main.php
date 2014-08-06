<?php

// Define the database tables to query
$info = array('pages', 'users', 'features', 'links', 'groups', 'media');

// Loop through the tables and query the databases for information
foreach($info as $i) {
    $query = $Theamus->DB->select_from_table($Theamus->DB->system_table($i), array('id'));
    $total[$i] = $Theamus->DB->count_rows($query);
}

// Loop through the information showing it all purty.
foreach ($total as $key => $val): ?>
    <div style='border:1px solid #EEE; padding: 5px 10px; margin: 5px 0 0;'>
        <span style='font-size:14pt; color: #AAA;'><?=$val?></span>
        <span style='padding: 0 5px;'><?=ucfirst($key)?></span>
    </div>
<?php endforeach; ?>