<?php

function home_type($array) {
    // Define the return array
    $return = array();

    // Explode the values
    if ($array == 'false') {
        $return['type'] = 'nooverride';
    } else {
        $return['type'] = $array['type'];
    }

    // Return the new array
    return $return;
}

$error = array();   // Define an empty error array
$query_data = array('table' => $tData->prefix.'groups');

$get = filter_input_array(INPUT_GET);   // Define a filtered 'get'

$id = isset($get['id']) ? $get['id'] : '';  // Define the id or it's default

// Query the database for the group
$query_group = $tData->select_from_table($query_data['table'], array(), array('operator' => '', 'conditions' => array('id' => $id)));

// Get the group's information
if ($query_group != false) {                                // Check for a successful query
    if ($tData->count_rows($query_group) > 0) {             // Check for results
        $group = $tData->fetch_rows($query_group);          // Define the group's information

        $permanent = $group['permanent'] == '1' ? true : false;
        $permissions = explode(',', $group['permissions']);

        if ((!$tUser->in_group('administrators') || !$tUser->is_admin()) && ($group['alias'] == 'everyone' || !$tUser->in_group($group['alias']))) {
            $error[] = 'You don\'t have permission to edit this group.';
        }

        if ($group['home_override'] != 'false') {
            $homeType = $tData->t_decode($group['home_override']);
        } else {
            $homeType['type'] = 'nooverride';
        }
    } else {
        $error[] = 'This group was not found.';
    }
} else {
    $error[] = 'There was an error querying the database.';
}

?>

<!-- Groups Tabs -->
<div class='admin-tabs'><?php echo $Groups->groups_tabs(FILE); ?></div>

<?php
if (!empty($error)) {
    alert_notify('danger', $error[0]);
}
?>

<!-- Form Results -->
<div id='group-result'></div>

<!-- Form -->
<form class='form-horizontal' id='group-form' onsubmit='return save_group();' style='margin-top: 15px;'>
    <input type='hidden' name='group_id' value='<?=$group['id']?>' />
    <div class='form-group'>
        <label class='control-label col-3' for='name'>Group Name</label>
        <div class='col-9'>
            <input type='text' class='form-control' name='name' id='name' autocomplete='off' value='<?=stripslashes($group['name'])?>' <?php if($permanent) { echo 'disabled'; } ?>>
        </div>
    </div>

    <?php if ($tUser->is_admin() && $tUser->in_group('administrators')): ?>
    <hr class='form-split'>

    <div class='form-group'>
        <label class='control-label col-3' for='permissions'>Permissions</label>
        <div class='col-9'>
            <select class='form-control' name='permissions' id='permissions' size='20' multiple='multiple'>
                <?php
                // Query the database for permissions
                $query_permissions = $tData->select_from_table($tData->prefix.'permissions', array('permission', 'feature'));

                // Loop through results
                $results = $tData->fetch_rows($query_permissions);
                foreach ($results as $permission) {
                    // Clean up the text
                    $permission_name    = ucwords(str_replace('_', ' ', $permission['permission']));
                    $permission_feature = ucwords(str_replace('_', ' ', $permission['feature']));

                    // Define checked
                    $checked = in_array($permission['permission'], $permissions) ? 'selected' : '';

                    // Show options
                    echo '<option value=\''.$permission['permission'].'\' '.$checked.'>'.$permission_feature.' - '.$permission_name.'</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <?php endif; ?>

    <hr class='form-split'>

    <h3 class='form-header'>Group Home Page</h3>
    <div class='col-12'>
        <div class='col-3' style='width:150px;'>
            <input type='hidden' id='type' value='<?=$homeType['type'] ?>' />
            <ul class='admin-columnlist'>
                <li><a href='#' onclick="return switch_type('nooverride');">No Override</a></li>
                <li><a href='#' onclick="return switch_type('page');">Page</a></li>
                <li><a href='#' onclick="return switch_type('feature');">Feature</a></li>
                <li><a href='#' onclick="return switch_type('custom');">Custom URL</a></li>
            </ul>
        </div>

        <div class='col-9'>
            <!-- No Override -->
            <?php
            // Define no home variables
            $homeNone['show'] = 'display:none;';
            if ($homeType['type'] == 'nooverride') {
                $homeNone['show'] = 'display:block;';
            }
            ?>
            <div id='nooverride' style='<?=$homeNone['show']?>width:auto;'>
                <p>User's in this group will go to the default home page that you have set up in Site Settings.</p>
                <p>To override the default home page, giving this group it's own home page is easy. Choose an item to the left and follow the instructions!</p>
            </div>
            <!-- End No Override -->

            <!-- Pages -->
            <?php
            // Define home page variables
            $homePage['show'] = 'display:none;';
            $homePage['id'] = '';
            if  ($homeType['type'] == 'page') {
                $homePage['show'] = 'display:block;';
                $homePage['id'] = $homeType['id'];
            }
            ?>
            <div id='page' style='<?=$homePage['show']?>width:auto;'>
                <div class='form-group'>
                    <label class='control-label col-3' for='pageid'>Home Page</label>
                    <div class='col-9'>
                        <select class='form-control' name='pageid' id='pageid'>
                            <?php
                            $query_pages = $tData->select_from_table($tData->prefix.'pages', array('id', 'title'));

                            // Make sure there are pages
                            if ($tData->count_rows($query_pages) > 0) {
                                // Grab the pages data and loop
                                $results = $tData->fetch_rows($query_pages);
                                foreach ($results as $page) {
                                    $homePage['selected'] = $homePage['id'] == $page['id'] ? 'selected' : '';
                                    echo '<option value=\''.$page['id'].'\' '.$homePage['selected'].'>'
                                         . $page['title'] . '</option>';
                                }
                            } else {
                                echo '<option>Error!</option>';
                            }
                            ?>
                        </select>
                        <p class='form-control-feedback'>Choosing this option will direct your users to a static page that you've created with the Pages feature within the Theamus system.</p>
                        <p class='form-control-feedback'>If you're looking to have a separate view for users that are logged in and logged out, check out the Session Views tab.</p>
                    </div>
                </div>
            </div>
            <!-- End Pages -->

            <!-- Features -->
            <?php
            // Define home feature variables
            $homeFeature['show'] = 'display:none;';
            $homeFeature['id'] = $homeFeature['file'] = '';
            if ($homeType['type'] == 'feature') {
                $homeFeature['show'] = 'display:block;';
                $homeFeature['id'] = $homeType['id'];
                $homeFeature['file'] = $homeType['file'];
            }
            ?>
            <div id='feature' style='<?=$homeFeature['show']?>width:auto;'>
                <div class='form-group'>
                    <label class='control-label col-3' for='featurename'>Feature</label>
                    <div class='col-9'>
                        <select class='form-control' name='featurename' id='featurename'>
                            <?php
                                // Define the features table and query the database
                                // for all available features
                                $query_features = $tData->select_from_table($tData->prefix.'features', array('id', 'alias', 'name'));

                                // Make sure there are features to show
                                if ($tData->count_rows($query_features) > 0) {
                                    $fi = 0; // Counter!
                                    // Grab the feature information and loop
                                    $results = $tData->fetch_rows($query_features);
                                    foreach ($results as $feature) {
                                        $homeFeature['selected'] = $homeFeature['id'] == $feature['id'] ? 'selected' : '';

                                        if ($homeFeature['id'] != '') {
                                            $query_homefeature = $tData->select_from_table($tData->prefix.'features', array('alias'), array(
                                                'operator'  => '',
                                                'conditions'=> array('id' => $homeFeature['id'])
                                            ));
                                            $sfeature = $tData->fetch_rows($query_homefeature);
                                            $firstFeature = $sfeature['alias'];
                                        } else {
                                            if ($fi == 0) {
                                                $firstFeature = $feature['alias'];
                                            }
                                        }
                                        echo '<option value=\'' . $feature['id'] . '\' ' . $homeFeature['selected'] . '>' .
                                             $feature['name'] . '</option>';
                                        $fi++; // Add to the counter
                                    }
                                } else {
                                    $firstFeature = false;
                                }
                                ?>
                        </select>
                    </div>
                </div>

                <?php if ($firstFeature != false): ?>
                <div class='form-group'>
                    <label class='control-label col-3' for='feature-file-list'>Feature File</label>
                    <div class='col-9'>
                        <select class='form-control' name='featurefile' id='feature-file-list'>
                            <?php
                            // Define the path to the feature's view files
                            $featurePath = path(ROOT . '/features/' . $firstFeature . '/views');

                            // Get all of the view files
                            $featureFiles = $tFiles -> scan_folder($featurePath, $featurePath);

                            // Make sure there are files
                            if (count($featureFiles) > 0) {
                                // Loop through all of the files
                                foreach ($featureFiles as $fFile) {
                                    $hfile = '';
                                    // Remove the file type
                                    $file = explode('.', $fFile);

                                    // Clean up the file name
                                    $fileName = str_replace('.php', '', $fFile);
                                    $fileName = str_replace('/', ' / ', $fileName);
                                    $fileName = str_replace('_', ' ', $fileName);
                                    $fileName = str_replace('-', ' ', $fileName);
                                    $fileName = ucwords($fileName);

                                    if ($homeFeature['file'] != '') {
                                        $hfile['selected'] = $homeFeature['file'].'.php' == $fFile ? 'selected' : '';
                                    } else if ($fFile == 'index.php') {
                                        $hfile['selected'] = 'selected';
                                    } else {
                                        $hfile['selected'] = '';
                                    }

                                    // Create the option
                                    echo '<option value=\'' . $file[0] . '\' ' . $hfile['selected'] . '>'
                                    . $fileName . '</option>';
                                }
                            } else {
                                echo '<option>There aren\'t any files.</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <hr class='form-split'>
                <p class='form-control-feedback'>If you really want to go to a feature, you just have to select it from the top selection box. That will take you to the index page by default. If you want or need to go to a specific page in the feature, just select a different selection.</p>
            </div>
            <!-- End Features -->

            <!-- Custom URL -->
            <?php
            $homeCustom['show'] = 'display:none;';
            $homeCustom['url'] = '';
            if ($homeType['type'] == 'custom') {
                $homeCustom['show'] = 'display:block;';
                $homeCustom['url'] = $homeType['url'];
            }
            ?>
            <div id='nocustom' style='display:none;width:auto;'>
                <div class='afi-col-nopad'>
                    You can't require a login to a custom url, that's just silly.
                    If you want to go to a custom url, you need to turn off the
                    required login. To do that,
                    <a href='#' onclick="return switch_type('login');">click here</a>.
                </div>
            </div>

            <div id='custom' style='<?=$homeCustom['show']?>width:auto;'>
                <div class='form-group'>
                    <label class='control-label col-3' for='customurl'>Custom URL</label>
                    <div class='col-9'>
                        <input type='text' class='form-control' id='customurl' name='customurl' autocomplete='off' value='<?php echo $homeCustom['url']; ?>'>
                    </div>

                    <hr class='form-split'>

                    <p class='form-control-feedback'>The Custom URL that you're inputting here is to a specific page within your site. It <b>cannot</b> go to an external site.</p>
                    <p class='form-control-feedback'>For example, you have a blog and you want to link to a specific post. Your URL would look like: http://www.theamus.com/blog/posts/this-is-a-post</p>
                    <p class='form-control-feedback'>All you need to input is: blog/posts/this-is-a-post</p>
                    <p class='form-control-feedback'>Everything else, like the base of the path, is assumed.</p>
                </div>
            </div>
        </div>
    </div>

    <hr class='form-split'>

    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Save Information</button>
    </div>
</form>

<script>
    admin_window_run_on_load('change_groups_tab');
</script>