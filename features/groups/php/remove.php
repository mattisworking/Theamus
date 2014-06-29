<?php

$error = array(); // Define an empty error array

$post = filter_input_array(INPUT_POST); // Define filtered post

$query_data = array(
    "group_table"   => $tData->prefix."groups",
    "user_table"    => $tData->prefix."users",
    "user_data"    => array(),
    "user_clause"  => array()
);

// Get group ID
$id = false;
if ($post['group_id'] != "") {
    $id = $post['group_id'];
    if (!is_numeric($id)) {
        $error[] = "The ID provided is invalid.";
    }
} else {
    $error[] = "There was an error finding the group ID.";
}

$group = false;
if ($id != false) {
    $query_check = $tData->select_from_table($query_data['group_table'], array("alias"), array(
        "operator"  => "",
        "conditions"=> array("id" => $id)
    ));

    if ($query_check != false) {
        if ($tData->count_rows($query_check) > 0) {
            $group = $tData->fetch_rows($query_check);
        } else {
            $error[] = "There was an issue finding this group in the database.";
        }
    } else {
        $error[] = "There was an error querying the database for this group.";
    }
}

if ($group != false) {
    $query_find_users = $tData->select_from_table($query_data['user_table'], array("id", "groups"), array(
        "operator"  => "",
        "conditions"=> array(
            "[%]groups" => "%".$group['alias']."%"
        )
    ));

    if ($query_find_users != false) {
        if ($tData->count_rows($query_find_users) > 0) {
            $results = $tData->fetch_rows($query_find_users);
            $users = isset($results[0]) ? $results : array($results);

            foreach ($users as $user) {
                $user_groups = explode(",", $user['groups']);
                $new_groups = array();
                foreach ($user_groups as $ug) {
                    if ($ug != $group['alias']) {
                        $new_groups[] = $ug;
                    }
                }

                $query_data['user_data'][] = array("groups" => implode(",", $new_groups));
                $query_data['user_clause'][] = array(
                    "operator"  => "",
                    "conditions"=> array("id" => $user['id'])
                );
            }
        }
    } else {
        $error[] = "There was an error querying the database for users.";
    }
}

// Show errors
if (!empty($error)) {
    notify("admin", "failure", $error[0]);
} else {
    $clean_users_query = true;
    if (!empty($query_data['user_data'])) {
        $clean_users_query = $tData->update_table_row($query_data['user_table'], $query_data['user_data'], $query_data['user_clause']);
    }

    if ($clean_users_query != false) {
        // Clear the db buffer before moving on
        if ($tData->use_pdo == true) {
            $clean_users_query->closeCursor();
        }

        $query = $tData->delete_table_row($query_data['group_table'], array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        if ($query != false) {
            notify("admin", "success", "This group has been removed.");
        } else {
            notify("admin", "failure", "There was an issue deleting this group.");
        }
    } else {
        notify("admin", "failure", "There was an error updating the users database table.");
    }
}
