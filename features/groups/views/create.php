<div class="admin_content-header">
    <span class="admin_content-header-img">
        <img id="admin_content-header-img" src="features/groups/img/create-groups.png" alt="" />
    </span>
    <div class="right" style="margin-top: -2px;">
        <input type="button" onclick="admin_go('accounts', 'groups/');" value="Go Back" />
    </div>
	<div class="admin_content-header-text">
        Create a New Group
    </div>
</div>

<div class="admin_page-content">
    <div id="group-result"></div>
    <form class="admin-form" id="group-form" onsubmit="return create_group();">
        <div class="admin-formrow">
            <div class="admin-formlabel">Group Name</div>
            <div class="admin-forminput">
                <input type="text" class="longtext" name="name" id="name" maxlength="100"
                       autocomplete="off" />
            </div>
            <div class="admin-forminfo">
                <span>This is the name that will appear to users, a general name.</span>
            </div>
        </div>

        <hr />

        <div class="admin-formrow">
            <div class="admin-formlabel afl-float">Permissions</div>
            <div class="admin-forminput">
                <select name="permissions" id="permissions" size="20" multiple="multiple">
                    <?php
                    // Query the database for permissions
                    $query = $tData->select_from_table($tData->prefix."_permissions", array("permission", "feature"));

                    // Loop through results
                    $results = $tData->fetch_rows($query);
                    foreach ($results as $permission) {
                        // Clean up the text
                        $permission_permission  = ucwords(str_replace("_", " ", $permission['permission']));
                        $permission_feature     = ucwords(str_replace("_", " ", $permission['feature']));

                        // Show options
                        if ($tUser->has_permission($permission['permission']) || ($tUser->is_admin() && $tUser->in_group("administrators"))) {
                            echo "<option value='".$permission['permission']."'>$permission_feature - $permission_permission</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="admin-forminfo">
                <span>All of the permissions selected here will be available to any
                    user in this group.</span>
            </div>
        </div>

        <hr />

        <div class="admin-formsubmitrow">
            <input type="submit" class="admin-greenbtn" value="Create Group" />
            <input type="button" class="admin-redbtn" value="Cancel"
                onclick="return admin_go('accounts', 'groups/')" />
        </div>
    </form>
</div>