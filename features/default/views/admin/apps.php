<?php

function add_homeapp_js($path, $homeapp) {
    if (isset($homeapp['js']) && is_array($homeapp['js'])) {
        foreach ($homeapp['js'] as $js) {
            if (file_exists($path."/".$js)) {
                add_js($path."/".$js);
            }
        }
    } else {
        return false;
    }
}

$query_error = false;
$user = $tUser->user;

for ($i = 1; $i <= 2; $i++) {
    $query = $tData->select_from_table("dflt_home-apps", array("path"), array(
        "operator"  => "AND",
        "conditions"=> array(
            "active"    => 1,
            "column"    => $i
        )
    ), "ORDER BY `position` ASC");

    if ($query != false) {
        $base_path = path(ROOT."/features/default/home-apps/");
        if ($tData->count_rows($query) > 0) {
            $x = 0;
            $results = $tData->fetch_rows($query);
            $results = isset($results[0]) ? $results : array($results);
    ?>
    <ul class="col-half left" id="column<?=$i?>">
    <?php
            foreach ($results as $app) {
                if (is_dir($base_path.$app['path'])) {
                    $path = $base_path.$app['path'];
                    $web_path = "features/default/home-apps/".$app['path'];
                    include $path."/config.php";
                    $x++;
    ?>
        <li id="<?=$app['path']?>=<?=$x?>">
            <div class="app_container" draggable="true">
                <div class="app_container-header handle"><?=$homeapp['block_title']?></div>
                <div class="app_container-content">
                    <?=$this->include_file($path."/main", false, true)?>
                </div>
            </div>
        </li>
    <?php
                }
                if (isset($web_path) && isset($homeapp)) {
                    add_homeapp_js($web_path, $homeapp);
                }
            }
    ?>
    </ul>
    <?php
        }
    } else {
        $query_error = true;
    }
}

if ($query_error == true) {
    alert_notify("danger", "There was an error querying the database.");
}