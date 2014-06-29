<?php

$post = filter_input_array(INPUT_POST);

$error = array();

$query_data = array(
    "pages_table"   => $tData->prefix."pages",
    "links_table"   => $tData->prefix."links"
);

// Check for a valid page ID
if (isset($post['page_id'])) {
    $id = $post['page_id'];
    if ($id != "" && is_numeric($id)) {
        // Get page from database
        $query_find_page = $tData->select_from_table($query_data['pages_table'], array("alias"), array(
            "operator"  => "",
            "conditions"=> array("id" => $id)
        ));

        // Check for a valid page in the database
        if ($query_find_page != false) {
            if ($tData->count_rows($query_find_page) > 0) {
                $page = $tData->fetch_rows($query_find_page); // Grab the info
            } else {
                $error[] = "There was an issue finding this page.";
            }
        }
    } else {
        $error[] = "Invalid ID type.";
    }
} else {
    $error[] = "What page are you talking about?";
}

$this->tData->use_pdo == false ? $this->tData->db->autocommit(false) : $this->tData->db->beginTransaction();

// Check if the user wants to remove the associated links
if ($post['remove_links'] == "true") {
    // Only proceed if we have a valid page
    if (isset($page)) {
        // Get all of the associated links related to this page
        $query_find_links = $tData->select_from_table($query_data['links_table'], array("id"), array(
            "operator"  => "",
            "conditions"=> array("[%]path" => $page['alias']."%")
        ));

        // Check for links
        if ($query_find_links != false) {
            if ($tData->count_rows($query_find_links) > 0) {
                $results            = $tData->fetch_rows($query_find_links);
                $links              = isset($results[0]) ? $results : array($results);
                $remove_links_data  = array();

                // Loop through all of the links, defining their removal queries
                foreach ($links as $link) {
                    $remove_links_data[] = array("operator" => "", "conditions" => array("id" => $link['id']));
                }

                // Remove the links
                $query_remove_links = $tData->delete_table_row($query_data['links_table'], $remove_links_data);

                if ($query_remove_links == false) {
                    $error[] = "There was an error removing the associated links from the database.  This page was not removed.";
                }
            }
        }
    }
}

// Show errors, if any
if (!empty($error)) {
    notify("admin", "failure", $error[0]);
} else {
    // Remove the page from the database
    $query = $tData->delete_table_row($query_data['pages_table'], array(
        "operator"  => "",
        "conditions"=> array("id" => $id)
    ));

    if ($query != false) {
        $this->tData->db->commit();
        notify("admin", "success", "This page has been deleted.");
    } else {
        $this->tData->use_pdo == false ? $this->tData->db->rollback() : $this->tData->db->rollBack();
        notify("admin", "failure", "There was an error removing this page from the database.");
    }
}