<?php

$pages_table = $tData->prefix."pages";
$query = $tData->select_from_table($pages_table, array("title", "views"), array(), "ORDER BY `views` DESC LIMIT 5");

if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        $pages = isset($results[0]) ? $results : array($results);

        foreach ($pages as $page) {
            $title = strlen($page['title']) > 10 ? substr($page['title'], 0, 10)."..." : $page['title'];
            $all_pages[$title] = $page['views'];
        }
        $all_pages = json_encode($all_pages);
?>
<canvas id="page_canvas" width="420" height="250" style="margin:0 auto; display:block;"></canvas>
<?php
    } else {
        alert_notify("info", "There are no pages to show!");
    }
} else {
    alert_notify("failure", "There was an issue querying the database.");
}
?>

<script type="text/javascript">
    function init(){if(typeof show_count_chart==="undefined"){setTimeout(function(){init()},50)}else{show_count_chart(<?php echo $all_pages; ?>)}}$(function(){init()})
</script>