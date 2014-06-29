<div class="admin_content-header">
    <span class="admin_content-header-img">
        <img id="admin_content-header-img" src="features/pages/img/create-pages.png" alt="" />
    </span>
    <div class="right" style="margin-top: -2px;">
        <input type="button" onclick="admin_go('pages', 'pages/');" value="Go Back" />
    </div>
	<div class="admin_content-header-text">Create a New Page</div>
</div>

<div class="admin_page-content">
    <div id="page-result"></div>
    <form class="admin-form" id="page-form" onsubmit="return create_page();">
        <div class="admin-formheader">Page Title</div>
        <div class="admin-formrow">
            <div class="admin-forminput">
                <input type="text" style="width:800px" name="title" placeholder="e.g.: Home Page" />
            </div>
        </div>

        <div class="admin-formheader">Page Content</div>
        <div class="admin-formrow">
            <?php $tEditor = new tEditor(array("id"=>"content")); ?>
        </div>

        <div class="admin-formheader">Page Options</div>
        <div class="admin-formcolumn" style="width: 350px;">
            <div class="admin-formrow">
                <div class="admin-formlabel">Use Theme Layout</div>
                <div class="admin-forminput">
                    <?=$Pages->get_selectable_layouts()?>
                </div>
            </div>
            <div class="admin-formrow">
                <div class="admin-formlabel afl-float">Permissable Groups</div>
                <div class="admin-forminput">
                    <select name="groups" multiple="multiple" size="7">
                    <?php
                        // Query the database for groups
                        $query = $tData->select_from_table($tData->prefix."groups", array("alias", "name"));

                        // Loop through all groups, showing as options
                        $results = $tData->fetch_rows($query);
                        foreach ($results as $group) {
                            $selected = $group['alias'] == "everyone" ? "selected" : "";
                            echo "<option ".$selected." value='".$group['alias']."'>"
                                    .$group['name']."</option>";
                        }
                    ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="admin-formcolumn" id="nav-links" style="display: none;">
            <input type="hidden" id="navigation" name="navigation" value="" />
            <div class="admin-formheader" style="margin: 0;">This layout allows navigation</div>
            <div id="link-area"></div>
            <div class="admin-formrow" style="padding-left:90px; margin-top:20px;">
                <a href="#" onclick="return add_new_link();">Add Another</a>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="admin-formheader">Link</div>
        <div class="admin-formrow">
            <div class="admin-formlabel afl-float">Create a Link</div>
            <div class="admin-forminput">
                <div class="admin-cboxwrapper">
                    <input type="checkbox" class="admin-switchcbox" name="create_link"
                        id="create_link">
                    <label class="admin-switchlabel yn" for="create_link">
                      <span class="admin-switchinner"></span>
                      <span class="admin-switchswitch"></span>
                    </label>
                </div>
            </div>
            <div class="admin-forminfo">
                This will create a link to go along with the page.  It will be
                placed in your sites default navigation area.
            </div>
        </div>

        <hr />

        <div class="admin-formsubmitrow">
            <input type="submit" value="Save" class="admin-greenbtn" />
            <input type="button" value="Cancel" onclick="admin_go('pages', 'pages/');" class="admin-redbtn" />
        </div>
    </form>
</div>