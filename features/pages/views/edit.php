<?php
$get = filter_input_array(INPUT_GET); // Clean the URL parameters

$error = false; // No errors to start out with
// Check for the existance of a page ID
if (isset($get['id'])) {
    $id = $get['id'];

    // Check the ID has a value
    if ($id != "") {
        // Query the database for the page
        $query = $tData->select_from_table($tData->prefix."pages", array(), array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        // Check for a valid query
        if ($query != false) {
            $page = $tData->fetch_rows($query); // Define the database informations
        } else {
            $error = "There was an error querying the database for the page.";
        }
    } else {
        $error = "Invalid ID value.";
    }
} else {
    $error = "No page ID was found.";
}
?>

<div class="admin_content-header">
    <span class="admin_content-header-img">
        <img id="admin_content-header-img" src="features/pages/img/edit-pages.png" alt="" />
    </span>
    <div class="right" style="margin-top: -2px;">
        <input type="button" onclick="admin_go('pages', 'pages/');" value="Go Back" />
    </div>
    <div class="admin_content-header-text">
        <?php if ($error == false): ?>
            Edit Page
        <?php else: ?>
            Something's wrong here...
        <?php endif ?>
    </div>
</div>

<div class="admin_page-content">
    <?php
    if ($error != false):
        notify("admin", "failure", $error);
    else:
        ?>
        <div id="page-result"></div>
        <form class="admin-form" id="page-form" onsubmit="return save_page();">
            <div class="admin-formheader">Page Title</div>
            <div class="admin-formrow">
                <div class="admin-forminput">
                    <input type="hidden" name="page_id" value="<?=$id?>" />
                    <input type="text" style="width:800px" name="title"
                           value="<?= $page['title'] ?>"
                           placeholder="e.g.: Home Page" />
                </div>
            </div>

            <div class="admin-formheader">Page Content</div>
            <div class="admin-formrow">
                <?php $tEditor = new tEditor(array("id"=>"content","text"=>$page['content'])); ?>
            </div>

            <div class="admin-formheader">Page Options</div>
            <div class="admin-formcolumn" style="width: 350px;">
                <div class="admin-formrow">
                    <div class="admin-formlabel">Use Theme Layout</div>
                    <div class="admin-forminput">
                        <?=$Pages->get_selectable_layouts($page['theme'])?>
                    </div>
                </div>
                <div class="admin-formrow">
                    <div class="admin-formlabel afl-float">Permissable Groups</div>
                    <div class="admin-forminput">
                        <select name="groups" multiple="multiple" size="7">
                            <?php
                            // Define the page groups
                            $pageGroups = explode(",", $page['groups']);

                            // Query the database for groups
                            $query = $tData->select_from_table($tData->prefix."groups", array("alias", "name"));
                            
                            // Loop through all groups, showing as options
                            $results = $tData->fetch_rows($query);
                            foreach ($results as $group) {
                                $selected = in_array($group['alias'], $pageGroups) ? "selected" : "";
                                echo "<option " . $selected . " value='" . $group['alias'] . "'>"
                                . $group['name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="admin-formcolumn" id="nav-links" style="display: none;">
                <input type="hidden" id="navigation" name="navigation" value="" />
                <div class="admin-formheader" style="margin: 0;">This layout allows navigation</div>
                <div id="link-area">
                    <?php
                    $themeLinks = explode(",", $page['navigation']);

                    $i = 1;
                    foreach ($themeLinks as $linkInfo) {
                        $link = explode("::", $linkInfo);
                        if ($link[0] == "") $link = array("", "");
                        ?>
                        <div class='link_row' id='link_row<?=$i?>'>
                            <div class='admin-formrow'>
                                <div class='admin-formlabel'>Link Text</div>
                                <div class='admin-forminput'>
                                    <input type='text' id='linktext-<?=$i?>' value="<?=$link[0] ?>" />
                                </div>
                            </div>
                            <div class='admin-formrow'>
                                <div class='admin-formlabel'>Link Path</div>
                                <div class='admin-forminput'>
                                    <input type='text' id='linkpath-<?=$i?>' value="<?=$link[1] ?>" />
                                </div>
                            </div>
                            <?php if ($i > 1): ?>
                            <div class='admin-forminfo'>
                                <a href='#' onclick="return remove_link('<?=$i?>');">Remove</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $i++;
                    }
                    ?>
                </div>
                <div class="admin-formrow" style="padding-left:90px; margin-top:20px;">
                    <a href="#" onclick="return add_new_link();">Add Another</a>
                </div>
            </div>
            <div class="clearfix"></div>

            <hr />

            <div class="admin-formsubmitrow">
                <input type="submit" value="Save" class="admin-greenbtn" />
                <input type="button" value="Cancel" onclick="admin_go('pages', 'pages/');" class="admin-redbtn" />
            </div>
        <?php endif ?>
    </form>
</div>