<?php

// Define and chec the group ID
$id = filter_input(INPUT_GET, 'id');
if ($id == '') die($Theamus->notify('danger', 'Failed to find the ID.'));

// Try to get the group information
try { $group = $Groups->get_group($id); }
catch (Exception $ex) { die($Theamus->notify('danger', $ex->get_message())); }

// Check the user's ability to even edit this group
if ((!$Theamus->User->in_group('administrators') || !$Theamus->User->is_admin()) && ($group['alias'] == 'everyone' || !$Theamus->User->in_group($group['alias']))) {
    die($Theamus->notify('danger', 'You do not have permission to edit this group.'));
}

// Define the home type
$group['home_override'] != 'false' ? $home_type = $Theamus->DB->t_decode($group['home_override']) : $home_type['type'] = 'nooverride';


unset($query); // Free the query variable

?>

<div class='admin-tabs'><?php echo $Groups->groups_tabs(FILE); ?></div>

<div id='group-result' style='margin-top: 15px;'></div>

<form class='form-horizontal' id='save-group-form' style='margin-top: 15px; width: 700px;'>
    <input type='hidden' name='id' value='<?=$group['id']?>' />
    <div class='form-group'>
        <label class='control-label col-3'>Group Name</label>
        <div class='col-9'>
            <p class='form-control-static'><?php echo $group['name']; ?></p>
        </div>
    </div>

    <?php if ($Theamus->User->is_admin() && $Theamus->User->in_group('administrators')): ?>
    <hr class='form-split'>

    <div class='form-group'>
        <label class='control-label col-3' for='permissions'>Permissions</label>
        <div class='col-9'>
            <select class='form-control' name='permissions' id='permissions' size='20' multiple='multiple'>
                <?php echo $Groups->get_permission_options(explode(',', $group['permissions'])); ?>
            </select>
        </div>
    </div>
    <?php endif; ?>

    <hr class='form-split'>

    <h2 class='form-header'>Group Home Page</h2>
    <div class='col-12'>
        <div class='col-3' style='width:150px;'>
            <input type='hidden' id='type' value='<?=$home_type['type'] ?>' />
            <ul style='list-style: none; padding: 0;'>
                <li><a href='#' name='type-link' data-type='nooverride'>No Override</a></li>
                <li><a href='#' name='type-link' data-type='page'>Page</a></li>
                <li><a href='#' name='type-link' data-type='feature'>Feature</a></li>
                <li><a href='#' name='type-link' data-type='custom'>Custom URL</a></li>
            </ul>
        </div>

        <div class='col-9'>
            <div id='nooverride' style='<?php echo $home_type['type'] == 'nooverride' ? 'display:block;' : 'display:none;'; ?> width:auto;'>
                <p>User's in this group will go to the default home page that you have set up in Site Settings.</p>
                <p>To override the default home page, giving this group it's own home page is easy. Choose an item to the left and follow the instructions!</p>
            </div>

            <div id='page' style='<?php echo $home_type['type'] == 'page' ? 'display:block;' : 'display:none;'; ?> width:auto;'>
                <div class='form-group'>
                    <label class='control-label col-3' for='pageid'>Home Page</label>
                    <div class='col-9'>
                        <select class='form-control' name='pageid' id='pageid'>
                            <?php echo $Groups->get_page_options($home_type); ?>
                        </select>
                        <p class='form-control-feedback'>Choosing this option will direct your users to a static page that you've created with the Pages feature within the Theamus system.</p>
                        <p class='form-control-feedback'>If you're looking to have a separate view for users that are logged in and logged out, check out the Session Views tab.</p>
                    </div>
                </div>
            </div>

            <div id='feature' style='<?php echo $home_type['type'] == 'feature' ? 'display:block;' : 'display:none;'; ?> width:auto;'>
                <div class='form-group'>
                    <label class='control-label col-3' for='featurename'>Feature</label>
                    <div class='col-9'>
                        <select class='form-control' name='featurename' id='featurename'>
                            <?php echo $Groups->get_feature_options($home_type); ?>
                        </select>
                    </div>
                </div>

                <?php if ($Groups->first_feature != false): ?>
                <div class='form-group'>
                    <label class='control-label col-3' for='feature-file-list'>Feature File</label>
                    <div class='col-9'>
                        <select class='form-control' name='featurefile' id='feature-file-list'>
                            <?php echo $Groups->get_feature_file_options($home_type); ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <hr class='form-split'>
                <p class='form-control-feedback'>If you really want to go to a feature, you just have to select it from the top selection box. That will take you to the index page by default. If you want or need to go to a specific page in the feature, just select a different selection.</p>
            </div>

            <div id='nocustom' style='display:none; width:auto;'>
                <div class='afi-col-nopad'>
                    You can't require a login to a custom url, that's just silly.
                    If you want to go to a custom url, you need to turn off the required login. To do that, <a href='#' name='type-link' data-type='login'>click here</a>.
                </div>
            </div>

            <div id='custom' style='<?php echo $home_type['type'] == 'custom' ? 'display:block;' : 'display:none;'; ?> width:auto;'>
                <div class='form-group'>
                    <label class='control-label col-3' for='customurl'>Custom URL</label>
                    <div class='col-9'>
                        <input type='text' class='form-control' id='customurl' name='customurl' autocomplete='off' value='<?php echo $home_type['type'] == 'custom' ? $home_type['url'] : ''; ?>'>
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
    admin_window_run_on_load('edit_group');
</script>