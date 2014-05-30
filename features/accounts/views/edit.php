<?php

function format_phone($number) {
    if ($number != "") {
        $phone = "1 ";
        $phone .= "(".substr($number, 0, 3).")";
        $phone .= " ".substr($number, 3, 3);
        $phone .= "-".substr($number, 6, 10);
    } else {
        $phone = "";
    }

    return $phone;
}

$error = array();   // Define an empty error array

$get = filter_input_array(INPUT_GET);   // Define a filtered 'get'

$id = isset($get['id']) ? $get['id'] : "";  // Define the id or it's default

// Query the database for the user
$query = $tData->select_from_table($tData->prefix."_users", array(), array("operator" => "", "conditions" => array("id" => $id)));

// Get the user's information
if ($query != false) {                              // Check for a successful query
    if ($tData->count_rows($query) > 0) {           // Check for results
        $user = $tData->fetch_rows($query);         // Define the user's information

        $permanent = $user['permanent'] == "1" ? true : false;
    } else {
        $error[] = "This user was not found.";
    }
} else {
    $error[] = "There was an error querying the database.";
}

?>

<div class="admin_content-header">
    <span class="admin_content-header-img">
        <img id="admin_content-header-img" src="features/accounts/img/edit-accounts.png" alt="" />
    </span>
    <div class="right" style="margin-top: -2px;">
        <input type="button" onclick="admin_go('accounts', 'accounts/');" value="Go Back" />
    </div>
	<div class="admin_content-header-text">
        <?php if (empty($error)): ?>
            Edit User "<?=$user['username']?>"
        <?php else: ?>
            Something's wrong here...
        <?php endif; ?>
    </div>
</div>

<div class="admin_page-content">
    <div id="user-result"></div>
	<?php
	if (!empty($error)):
		 notify("admin", "failure", $error[0]
            ." -- <a href='#' onclick=\"return admin_go('accounts', 'accounts/');\">Go Back</a>");
	else:
        if ($permanent == true) {
            notify("admin", "info", "This is a permanent user, some options may be disabled.");
        }
    ?>
    <form class="admin-form" id="user-form" onsubmit="return save_user();">
        <div class="admin-formheader">Login Information</div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Username</div>
            <div class="admin-forminput">
                <input type="text" disabled="disabled" value="<?=$user['username']?>" />
                <input type="hidden" name="id" value="<?=$id?>" />
            </div>
            <div class="admin-forminfo">
                This is the user's username. It cannot be changed once the account
                has been created.
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel sfl-float">Change Password</div>
            <div class="admin-forminput">
                <div class="admin-cboxwrapper">
                    <input type="checkbox" class="admin-switchcbox" name="change_pass"
                      id="changePass" onchange="toggle_pass();">
                    <label class="admin-switchlabel yn" for="changePass">
                      <span class="admin-switchinner"></span>
                      <span class="admin-switchswitch"></span>
                    </label>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div id="passwords" style="display:none;">
            <hr />
            <div class="admin-formrow">
                <div class="admin-formlabel">New Password</div>
                <div class="admin-forminput">
                    <input type="password" id="password" maxlength="30" name="password" />
                </div>
                <div class="admin-forminfo">
                    New password for the user to login with.
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
            <hr />
        </div>

        <div class="admin-formheader">
            User's Name
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">First Name</div>
            <div class="admin-forminput">
                <input type="text" name="firstname" value="<?=$user['firstname']?>" />
            </div>
            <div class="admin-forminfo">
                The user's first name.
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Last Name</div>
            <div class="admin-forminput">
                <input type="text" name="lastname" value="<?=$user['lastname']?>" />
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
                <input type="text" name="email" maxlength="100" value="<?=$user['email']?>" />
            </div>
            <div class="admin-forminfo">
                Looks something like: "roadrunner@acme.org"
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Phone Number</div>
            <div class="admin-forminput">
                <input type="text" name="phone" maxlength="17" value="<?=format_phone($user['phone'])?>" />
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
                        <?php
                        $genders = array("m"=>"Male", "f"=>"Female");
                        foreach ($genders as $key=>$val) {
                            if ($user['gender'] == $key) {
                                echo "<option value='".$key."' selected>".$val."</option>";
                            } else {
                                echo "<option value='".$key."'>".$val."</options>";
                            }
                        }
                        ?>
                    </select>
                </label>
            </div>
        </div>
        <div class="admin-formrow">
            <div class="admin-formlabel">Birthday</div>
            <?php
            $birthday = explode("-", $user['birthday']);
            ?>
            <div class="admin-forminput">
                <label class="admin-selectlabel">
                    <select name="bday-m">
                        <?php
                        for ($i=1; $i<=12; $i++) {
                            $selected = $i == $birthday[1] ? "selected" : "";
                            echo "<option ".$selected." value='".$i."'>".$i."</option>";
                        }
                        ?>
                    </select>
                </label>
                <label class="admin-selectlabel">
                    <select name="bday-d">
                        <?php
                        for ($i=1; $i<=31; $i++) {
                            $selected = $i == $birthday[2] ? "selected" : "";
                            echo "<option ".$selected." value='".$i."'>".$i."</option>";
                        }
                        ?>
                    </select>
                </label>
                <label class="admin-selectlabel">
                    <select name="bday-y">
                        <?php
                        for ($i=2014; $i>=1940; $i--) {
                            $selected = $i == $birthday[0] ? "selected" : "";
                            echo "<option ".$selected." value='".$i."'>".$i."</option>";
                        }
                        ?>
                    </select>
                </label>
            </div>
        </div>

        <?php if ($tUser->is_admin() && $tUser->in_group("administrators")): ?>
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
                    $checked = in_array($group['alias'], $groups) ? "selected" : "";
                    if ($tUser->in_group($group['alias']) || ($tUser->is_admin() && $tUser->in_group("administrators"))) {
                        echo "<option ".$checked." value=\"".$group['alias']."\">".$group['name']."</option>";
                    }
                }
                ?>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <div class="admin-formheader">Admin/Active User</div>
        <?php if ($tUser->is_admin() && $tUser->in_group("administrators")): ?>
        <div class="admin-formrow">
            <div class="admin-formlabel afl-float">Administrator</div>
            <div class="admin-forminput">
                <div class="admin-cboxwrapper">
                    <input type="checkbox" class="admin-switchcbox" name="is_admin"
                      id="is_admin" <?php
                      if ($permanent == true): echo "checked disabled";
                      elseif ($user['admin'] == "1"): echo "checked";
                      endif;
                      ?>>
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

        <div class="admin-formrow">
            <div class="admin-formlabel afl-float">Active User</div>
            <div class="admin-forminput">
                <div class="admin-cboxwrapper">
                    <input type="checkbox" class="admin-switchcbox" name="active"
                      id="active" <?php
                      if ($permanent == true): echo "checked disabled";
                      elseif ($user['active'] == "1"): echo "checked";
                      endif;
                      ?>>
                    <label class="admin-switchlabel yn" for="active">
                      <span class="admin-switchinner"></span>
                      <span class="admin-switchswitch"></span>
                    </label>
                </div>
            </div>
            <div class="admin-forminfo">
                Can this user use this site?
            </div>
        </div>

        <hr />

        <div class="admin-formsubmitrow">
            <input type="submit" value="Save" class="admin-greenbtn" />
            <input type="button" value="Cancel" onclick="admin_go('accounts', 'accounts/');"
                class="admin-redbtn" />
        </div>
    </form>
	<?php endif; ?>
</div>