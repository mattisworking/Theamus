<?php

class Media {
    protected $Theamus;
    protected $allowed_media = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'ico');
    protected $images = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'ico');
    protected $images_folder = '';
    protected $objects_folder = '';


    /**
     * Connects to Theamus
     *
     * @param object $t
     * @return
     */
    public function __construct($t) {
        $this->Theamus = $t;

        $this->images_folder = ROOT.'/media/images/';
        $this->objects_folder = ROOT.'/media/objects/';

        return;
    }

    /**
     * Define the media tabs and show the 'current' tab respectively
     *
     * @param string $file
     * @return string
     */
    public function media_tabs($file = '') {
        // Define the tabs and their options
        $tabs = array(
            array('All Media', 'media/index.php', 'Theamus Media'),
            array('Add Media', 'media/add-media.php', 'Add Media'));

        // Return the HTML tabs
        return $this->Theamus->Theme->generate_admin_tabs("media-tab", $tabs, $file);
    }


    /**
     * Uploads a media file to Theamus
     *
     * @return boolean
     * @throws Exception
     */
    public function upload_media() {
        // Check for an administrator with the proper permissions
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('add_media')) {
            die('Only administrators with the proper permissions can Add Theamus Media.');
        }

        // Define the file name information
        $file = explode('.', strtolower($_FILES['upload_file']['name']));

        // Define the file extension
        $extension = $file[count($file) - 1];

        // Check the file extension
        if (!in_array($extension, $this->allowed_media)) throw new Exception('Invalid file type. ('.$extension.')');

        // Define the 'hashed' file name as the alias
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $temp_alias = '';
        for ($i = 0; $i < 32; $i++) {
             $temp_alias .= $characters[rand(0, strlen($characters) - 1)];
        }
        $alias = md5($temp_alias.time()).'.'.$extension;

        $type = 'image'; // Initialize the file type variable

        // Check the extension to find out what kind of a file it is (image, object, video, etc)
        if (!in_array($extension, $this->images)) $type = 'object';

        // Try to move the file to the respective folder
        if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $this->Theamus->file_path($this->{$type.'s_folder'}.$alias))) {
            $this->Theamus->Log->system('Failed to upload media to the media/'.$type.'s/ folder. Check file permissions.');
            throw new Exception('Failed to upload to the media folder.');
        }

        // Upload the information to the database
        $query = $this->Theamus->DB->insert_table_row(
            $this->Theamus->DB->system_table('media'),
            array('path'    => $type.'s/'.$alias,
                'file_name' => $_FILES['upload_file']['name'],
                'file_size' => $_FILES['upload_file']['size'],
                'type'      => $type));

        // Check the query for errors
        if (!$query) {
            // Delete the file
            unlink($this->Theamus->file_path(${$type.'s_folder'}.$alias));

            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error

            throw new Exception('Failed to save the upload.');
        }

        return true; // Return true!
    }


    /**
     * Gets media information from the database
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get_media($id) {
        // Query the database for media with the given ID
        $query = $this->Theamus->DB->select_from_table(
            $this->Theamus->DB->system_table('media'),
            array(),
            array('operator' => '',
                'conditions' => array('id' => $id)));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            throw new Exception('Failed to get media.');
        }

        // Check the query for results
        if ($this->Theamus->DB->count_rows($query) == 0) throw new Exception('Could not find Media.');

        // Return the information from the query
        return $this->Theamus->DB->fetch_rows($query);
    }


    /**
     * Removes a media item from the database and the media folder
     *
     * @param array $args
     * @return boolean
     * @throws Exception
     */
    public function remove_media($args) {
        // Check for an administrator with proper permissions
        if (!$this->Theamus->User->is_admin() || !$this->Theamus->User->has_permission('remove_media')) {
            die('Only administrators with the proper permissions can Remove Media.');
        }

        // Check for an ID
        if (!isset($args['id']) || $args['id'] == '' || !is_numeric($args['id'])) throw new Exception('Invalid ID.');

        // Get the media item information from the database
        $media = $this->get_media($args['id']);

        // Query the database, removing the information there
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->beginTransaction() : $this->Theamus->DB->connection->autocommit(true);
        $query = $this->Theamus->DB->delete_table_row(
            $this->Theamus->DB->system_table('media'),
            array('operator' => '',
                'conditions' => array('id' => $args['id'])));

        // Check the query for errors
        if (!$query) {
            $this->Theamus->Log->query($this->Theamus->DB->get_last_error()); // Log the query error
            throw new Exception('Failed to remove media.');
        }

        // Try to remove the file
        if (!unlink($this->Theamus->file_path(ROOT.'/media/'.$media['path']))) {
            $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->rollBack() : $this->Theamus->DB->connection->rollback();
            $this->Theamus->Log->system('Failed to remove a media file. Check file permissions.');
            throw new Exception('Failed to remove media.');
        }

        // Commit to the DB and return true!
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();
        return true;
    }
    
    
    public function get_media_listing($page_number = 1) {
        $result_limit = 8;
        $conditions = array();
        
        $count_query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table("media"));
        
        if (!$count_query) throw new Exception("Failed to get a total count on the media.");
        
        $this->total_result_count = $this->Theamus->DB->count_rows($count_query);
        
        if ($this->total_result_count > 200) $query_limit['count'] = 200;
        
        $query_limit = array(
            "start" => ($result_limit * $page_number) - $result_limit,
            "count" => $result_limit
        );
        
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table("media"),
                array(),
                (!empty($conditions) ? array("operator" => "&&", "conditions" => $conditions) : array()),
                "LIMIT {$query_limit['start']}, {$query_limit['count']}");
        
        if (!$query) {
            throw new Exception();
        }
        
        $results = $this->Theamus->DB->fetch_rows($query);
        return isset($results[0]) ? $results : array($results);
    }
    
    public function get_page_links($current_page = 1) {
        $result_limit = 8;
        $total_pages = ceil($this->total_result_count / $result_limit);
        $pad_size = 2;
        
        $page_links = array();
        
        $numbers = array("left" => array("count" => 0, "links" => array()), "right" => array("count" => 0, "links" => array()));

        for ($i = ($current_page - 1); $i >= ($current_page - $pad_size); $i--) {
            if ($i <= 0) continue;
            $numbers['left']['count'] = $numbers['left']['count'] + 1;
            $numbers['left']['links'][] = "<a href='#'>{$i}</a>";
        }
        $numbers['left']['links'] = array_reverse($numbers['left']['links']);
        $numbers['left']['links'][] = "<a href='#' class='media_listing-current-page'>{$current_page}</a>";

        for ($i = ($current_page + 1); $i <= ($current_page + $pad_size); $i++) {
            if ($i > $total_pages) continue;
            $numbers['right']['count'] = $numbers['right']['count'] + 1;
            $numbers['right']['links'][] = "<a href='#'>{$i}</a>";
        }

        if (!empty($numbers['right']['links'])) $page_links = array_merge($numbers['right']['links'], $page_links);
        if (!empty($numbers['left']['links'])) $page_links = array_merge($numbers['left']['links'], $page_links);
        if (($current_page - 1) > 0) array_unshift($page_links, "<a href='#' class='media_listing-previous-page'>&lt;</a> ");
        if ($current_page < $total_pages) $page_links[] = " <a href='#' class='media_listing-next-page'>&gt;</a>";

        return $page_links;
    }
}