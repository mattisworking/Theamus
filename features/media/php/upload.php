<?php

$error = array();
$file = $_FILES['file'];
$allowed_media = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf');
$upload_path = ROOT.'/media/images/';

$name_array = explode('.', strtolower($file['name']));
$extension = $name_array[count($name_array) - 1];

if (in_array($extension, $allowed_media)) {
    $alias = md5(time()).'.'.$extension;

    $name = $file['name'];
    $size = $file['size'];
} else {
    $error[] = 'This type of file is not allowed here. ($extension)';
}

// Define the media type
$images = array('jpg', 'jpeg', 'png', 'gif', 'webp');
if (in_array($extension, $images)) {
    $type = 'image';
} elseif ($extension == 'pdf') {
    $type = 'object';
} else {
    $type = 'image';
}

if (!empty($error)) {
    echo 'Failed. <span class="media_error-title" title=\''.$error[0].'\'>?</span>';
} else {
    if (move_uploaded_file($file['tmp_name'], $upload_path.$alias)) {
        $query_data = array(
            'table' => $tData->prefix.'media',
            'data'  => array(
                'path'      => $alias,
                'file_name' => $name,
                'file_size' => $size,
                'type'      => $type
            )
        );

        $query = $tData->insert_table_row($query_data['table'], $query_data['data']);

        if ($query != false) {
            echo 'Completed';
        } else {
            echo 'Failed. <span class="media_error-title" title="There was an error adding the media to the database.">?</span>';
            unlink($upload_path.$alias);
        }
    } else {
        echo 'Failed. <span class="media_error-title" title="There was an error moving the uploaded file.">?</span>';
    }
}