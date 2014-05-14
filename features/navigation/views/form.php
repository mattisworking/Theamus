<?php

// Clean the get input
$post = filter_input_array(INPUT_POST);

// Define the types of paths we have to offer
$paths = array("null","url", "page", "feature", "js");
// Loop through the paths and set their appearance based on the type we need
foreach ($paths as $path) {
    if ($post['type'] == $path) {
        $show[$path] = "display: block;";
    } else {
        $show[$path] = "display: none;";
    }
}

// Define the default values of the form elements
$url = $page = $feature = $js = $file = "";

$post['text'] = urldecode($post['text']);
$post['path'] = urldecode($post['path']);
$post['position'] = urldecode($post['position']);

// Go through the types of links we could have and assign their values appropriately
switch ($post['type']) {
    case "null":
        $null = $post['path'];
        break;

    case "url":
        $url = $post['path'];
        break;

    case "page":
        $page = trim($post['path'], "/");
        break;

    case "feature":
        $f_info = explode("/", $post['path']);
        $feature = $f_info[0];
        array_shift($f_info);
        $file = implode("/", $f_info);
        break;

    case "js":
        $js = $post['path'];
        break;
}

?>

<div id="link-result"></div>
    <form class="admin-form" id="link-form">
        <div class="admin-formheader">Link Text</div>
        <div class="admin-formrow">
            <div class="admin-forminput">
                <input type="hidden" name="link-id" value="<?=$post['id']?>" />
                <input type="text" name="text" style="width: 500px;" maxlength="150"
                       placeholder="My Cat's Page" autocomplete="off"
                       value="<?=$post['text']?>"/>
            </div>
        </div>

        <div class="admin-formheader">Link Path</div>
        <div class="admin-formrow">
            <div class="admin-formcolumn" style="width: 200px;">
                <input type="hidden" name="path-type" id="path-type" value="path-<?=$post['type']?>" />
                <ul class="admin-columnlist">
                    <li><a href="" name="path" id="path-null">Title Box</a></li>
                    <li><a href="#" name="path" id="path-url">Website URL</a></li>
                    <li><a href="#" name="path" id="path-page">Theamus Page</a></li>
                    <li><a href="#" name="path" id="path-feature">Theamus Feature</a></li>
                    <li><a href="#" name="path" id="path-js">Javascript</a></li>
                </ul>
            </div>
            <div class="admin-formcolumn">
                <div id="path-null-wrapper" style="<?=$show['null']?>">
                    <div class="admin-formrow">
                        <div class="admin-forminput">
                            <input type="hidden" name="null" value='null' />
                        </div>
                        <div class="admin-forminfo">
                            A Title Box will not take any type of action when it is clicked
                        </div>
                    </div>
                </div>

                <div id="path-url-wrapper" style="<?=$show['url']?>">
                    <div class="admin-formrow">
                        <div class="admin-formlabel">URL Path</div>
                        <div class="admin-forminput">
                            <input type="text" name="url" autocomplete="off" value="<?=$url?>" />
                        </div>
                        <div class="admin-forminfo">
                            In order for your link to work properly, you need to include
                            the entire path.<br />
                            For example: http://www.theamus.com/
                        </div>
                    </div>
                </div>

                <div id="path-page-wrapper" style="<?=$show['page']?>">
                    <input type="hidden" id="page" value="<?=$page?>" />
                    <div class="admin-formrow">
                        <div class="admin-formlabel">Theamus Page</div>
                        <div class="admin-forminput">
                            <label class="admin-selectlabel">
                                <!-- Updated via AJAX -->
                                <select name="page" id="page-select"></select>
                            </label>
                        </div>
                        <div class="admin-forminfo">
                            No worries here, just do the page and the system will
                            automatically format the link for you!
                        </div>
                    </div>
                </div>

                <div id="path-feature-wrapper" style="<?=$show['feature']?>">
                    <input type="hidden" id="feature" value="<?=$feature?>" />
                    <input type="hidden" id="feature-file" value="<?=$file?>" />
                    <div class="admin-formrow">
                        <div class="admin-formlabel">Theamus Feature</div>
                        <div class="admin-forminput">
                            <label class="admin-selectlabel">
                                <!-- Updated via AJAX -->
                                <select name="feature" id="feature-select"></select>
                            </label>
                        </div>
                        <div class="admin-forminfo">
                            Here's a list of all the features you can access.<br />
                            If you haven't noticed, you can link to any of them.
                        </div>
                    </div>

                    <div class="admin-formrow">
                        <div class="admin-formlabel">Feature File</div>
                        <div class="admin-forminput">
                            <label class="admin-selectlabel">
                                <!-- Updated via AJAX -->
                                <select name="file" id="feature-file-select"></select>
                            </label>
                        </div>
                        <div class="admin-forminfo">
                            This list will change based on the feature you've selected
                            above.<br />
                            If a file is in a folder, it will show up as Folder: File
                        </div>
                    </div>
                </div>

                <div id="path-js-wrapper" style="<?=$show['js']?>">
                    <div class="admin-formrow">
                        <div class="admin-formlabel">Javascript</div>
                        <div class="admin-forminput">
                            <input type="text" name="js" autocomplete="off"
                                   style="width: 350px;" value="<?=htmlspecialchars($js)?>" />
                        </div>
                        <div class="admin-forminfo">
                            Whatever you decide to put here will be run as javascript
                            when the link is clicked on.<br />
                            Imagine the link being: 'javascript:&lt;input&gt;'
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="admin-formheader">Page Position</div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Position</div>
            <div class="admin-forminput">
                <label class="admin-selectlabel">
                    <select name="position"><?=$Navigation->get_positions_select($post['position'])?></select>
                </label>
            </div>
            <div class="admin-forminfo">
                This determines where the position of this link will be placed
                on the theme.
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Child Of</div>
            <div class="admin-forminput">
                <label class="admin-selectlabel">
                    <select name="child_of"><?=$Navigation->get_children_select($post['child_of'])?></select>
                </label>
            </div>
            <div class="admin-forminfo">
                Creates a sub-menu to the parent link selected.
            </div>
        </div>

        <div class="admin-formheader">Extra Information</div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Link Weight</div>
            <div class="admin-forminput">
                <label class="admin-selectlabel">
                    <select name="weight">
                        <?php
                        for ($i=1; $i<=100; $i++):
                            $selected = $i == $post['weight'] ? "selected" : "";
                        ?>
                        <option value="<?=$i?>" <?=$selected?>><?=$i?></option>
                        <?php endfor; ?>
                    </select>
                </label>
            </div>
            <div class="admin-forminfo">
                The link weight determines the position of where the link is placed
                in the navigation.
            </div>
        </div>

        <div class="admin-formrow">
            <div class="admin-formlabel afl-float">Groups</div>
            <div class="admin-forminput">
                <input type="hidden" id="groups" value="<?=$post['groups']?>" />
                <!-- Updated via AJAX -->
                <select name="groups" id="group-select" multiple="multiple" size="10"></select>
            </div>
        </div>

        <hr />

        <div class="admin-formsubmitrow">
            <input type="submit" class="admin-greenbtn" value="Save" />
            <input type="button" class="admin-redbtn" value="Cancel" name="cancel" />
        </div>
    </form>