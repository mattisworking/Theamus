<?php

class Media {
    protected $Theamus;
    protected $allowed_media = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf');
    protected $images = array('jpg', 'jpeg', 'png', 'gif', 'webp');
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
            array('All Media', 'index.php', 'Theamus Media'),
            array('Add Media', 'add-media.php', 'Add Media')
        );

        $return_tabs = array(); // Empty return array to add to

        // Loop through all of the tabs defined above and assign them to li items/links
        foreach ($tabs as $tab) {
            $class = $tab[1] == $file ? 'class=\'current\'' : ''; // Define the current tab
            $return_tabs[] = '<li '.$class.'><a href=\'#\' name=\'media-tab\' data-file=\'/media/'.str_replace('.php', '', $tab[1]).'/\' data-title=\''.$tab[2].'\'>'.$tab[0].'</a></li>';
        }

        // Return the tabs to the page
        return '<ul>'.implode('', $return_tabs).'</ul>';
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
            $this->Theamus->Log->error('Failed to upload media to the media/'.$type.'s/ folder. Check file permissions.');
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
            $this->Theamus->Log->error('Failed to remove a media file. Check file permissions.');
            throw new Exception('Failed to remove media.');
        }

        // Commit to the DB and return true!
        $this->Theamus->DB->use_pdo == true ? $this->Theamus->DB->connection->commit() : $this->Theamus->DB->connection->commit();
        return true;
    }
}