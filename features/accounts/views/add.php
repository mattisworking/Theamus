<div class="admin_content-header">
    <span class="admin_content-header-img">
        <img id="admin_content-header-img" src="features/accounts/img/add-accounts.png" alt="" />
    </span>
    <div class="right" style="margin-top: -2px;">
        <input type="button" onclick="admin_go('accounts', 'accounts/');" value="Go Back" />
    </div>
	<div class="admin_content-header-text">Add a New User</div>
</div>

<div class="admin_page-content">
    <div id="user-result"></div>
    <form class="admin-form" id="user-form" onsubmit="return add_user();">
        <div class="admin-formheader">Login Information</div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Username</div>
            <div class="admin-forminput">
                <input type="text" name="username" maxlength="50" />
            </div>
            <div class="admin-forminfo">
                This is the user's username. It cannot be changed once the account
                has been created.
            </div>
        </div>

        <hr />
        <div class="admin-formrow">
            <div class="admin-formlabel">Password</div>
            <div class="admin-forminput">
                <input type="password" id="password" maxlength="30" name="password" />
            </div>
            <div class="admin-forminfo">
                Password for the user to login with.
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Repeat Password</div>
            <div class="admin-forminput">
                <input type="password" maxlength="30" name="repeat_password" />
            </div>
            <div class="admin-forminfo">
                This should match the password above.
            </div>
        </div>

        <div class="admin-formheader">
            User's Name
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">First Name</div>
            <div class="admin-forminput">
                <input type="text" name="firstname" maxlength="50" />
            </div>
            <div class="admin-forminfo">
                The user's first name.
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Last Name</div>
            <div class="admin-forminput">
                <input type="text" name="lastname" maxlength="50" />
            </div>
            <div class="admin-forminfo">
                The user's last name.
            </div>
        </div>

        <div class="admin-formheader">
            Contact Information
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Email Address</div>
            <div class="admin-forminput">
                <input type="text" name="email" maxlength="100" />
            </div>
            <div class="admin-forminfo">
                Looks something like: "roadrunner@acme.org"
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Phone Number</div>
            <div class="admin-forminput">
                <input type="text" name="phone" maxlength="17" />
            </div>
            <div class="admin-forminfo">
                Can be a cell, work, home, or fax number.
            </div>
        </div>

        <div class="admin-formheader">
            Other Information
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Gender</div>
            <div class="admin-forminput">
                <label class="admin-selectlabel">
                    <select name="gender">
                        <option value="m">Male</option>
                        <option value="f">Female</option>
                    </select>
                </label>
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Birthday</div>
            <div class="admin-forminput">
                <label class="admin-selectlabel">
                    <select name="bday-m">
                        <?php
                        for ($i=1; $i<=12; $i++) {
                            echo "<option value='".$i."'>".$i."</option>";
                        }
                        ?>
                    </select>
                </label>
                <label class="admin-selectlabel">
                    <select name="bday-d">
                        <?php
                        for ($i=1; $i<=31; $i++) {
                            echo "<option value='".$i."'>".$i."</option>";
                        }
                        ?>
                    </select>
                </label>
                <label class="admin-selectlabel">
                    <select name="bday-y">
                        <?php
                        for ($i=2014; $i>=1940; $i--) {
                            echo "<option value='".$i."'>".$i."</option>";
                        }
                        ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="admin-formheader">User Groups</div>
        <div class="admin-formrow">
            <div class="admin-formlabel afl-float">Groups</div>
            <div class="admin-forminput">
                <select name="groups" multiple="multiple" size="7">
                <?php
               // Query the database for groups
                $query = $tData->select_from_table($tData->prefix."_groups", array("alias", "name"));

                $groups = explode(",", $user['groups']);

                // Loop through all groups, showing as options
                $results = $tData->fetch_rows($query);
                foreach ($results as $group) {
                    $selected = $group['alias'] == "everyone" ? "selected" : "";
                    if ($tUser->in_group($group['alias']) || ($tUser->is_admin() && $tUser->in_group("administrators"))) {
                        echo "<option ".$selected." value=\"".$group['alias']."\">".$group['name']."</option>";
                    }
                }
                ?>
                </select>
            </div>
        </div>

        <?php if ($tUser->is_admin() && $tUser->in_group("administrators")): ?>
        <div class="admin-formheader">Admin User</div>
        <div class="admin-formrow">
            <div class="admin-formlabel afl-float">Administrator</div>
            <div class="admin-forminput">
                <div class="admin-cboxwrapper">
                    <input type="checkbox" class="admin-switchcbox" name="is_admin"
                      id="is_admin" />
                    <label class="admin-switchlabel yn" for="is_admin">
                      <span class="admin-switchinner"></span>
                      <span class="admin-switchswitch"></span>
                    </label>
                </div>
            </div>
            <div class="admin-forminfo">
                If this is set as "yes", the user can get to the administration panel
                regardless of being in the administrator group.
            </div>
        </div>
        <?php endif; ?>

        <hr />

        <div class="admin-formsubmitrow">
            <input type="submit" value="Save" class="admin-greenbtn" />
            <input type="button" value="Cancel" onclick="admin_go('accounts', 'accounts/');"
                class="admin-redbtn" />
        </div>
    </form>
</div>