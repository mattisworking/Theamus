<?php

function add_homeapp_js($Theamus, $path, $homeapp) {
    if (isset($homeapp['js']) && is_array($homeapp['js'])) {
        foreach ($homeapp['js'] as $js) {
            if (file_exists($path.'/'.$js)) {
                $Theamus->load_js_file($path.'/'.$js);
            }
        }
    } else {
        return false;
    }
}

$query_error = false; // Initialize the query error variable

// Loop through the possible columns
for ($i = 1; $i <= 2; $i++) {
    // Query the database for the apps in this column
    $query = $Theamus->DB->select_from_table('dflt_home-apps', array('path'), array(
        'operator'  => 'AND',
        'conditions'=> array(
            'active'    => 1,
            'column'    => $i
        )
    ), 'ORDER BY `position` ASC');

    // Check the query for errors
    if ($query != false) {
        // Define the base path to the apps
        $base_path = $Theamus->file_path(ROOT.'/features/default/home-apps/');

        // Check the query for results
        if ($Theamus->DB->count_rows($query) > 0) {
            $x = 0; // Initialize X for the app counter

            // Define the app information
            $results = $Theamus->DB->fetch_rows($query);
            $apps = isset($results[0]) ? $results : array($results);
            ?>
            <ul class='col-half left' id='column<?=$i?>'>
            <?php

            // Loop through the apps
            foreach ($apps as $app) {
                // Check if the app exists
                if (is_dir($base_path.$app['path'])) {
                    // Define the file and web path information
                    $path       = $base_path.$app['path'];
                    $web_path   = 'features/default/home-apps/'.$app['path'];

                    // Load the app's configuration file (for the block_title)
                    include $path.'/config.php';

                    $x++; // Add to the app count
            ?>

                <li id='<?=$app['path']?>=<?=$x?>'>
                    <div class='app_container' draggable='true'>
                        <div class='app_container-header handle'><?=$homeapp['block_title']?></div>
                        <div class='app_container-content'>
                            <?=$this->include_file($path.'/main', false, true)?>
                        </div>
                    </div>
                </li>

            <?php
                }

                // Load the javascript for the home app if possible
                if (isset($web_path) && isset($homeapp)) add_homeapp_js($Theamus, $web_path, $homeapp);
            }
            ?>
            </ul>
    <?php
        }
    } else $query_error = true;
}

if ($query_error == true) $Theamus->notify('danger', 'There was an error querying the database.');