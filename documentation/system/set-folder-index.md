Setting Custom Folder Index Files
==

In the feature's `files.info.php` file, you can customize the name of the index file by the one simple function call of `$Theamus->Call->set_folder_index( $file_name )`.

Here's a few examples:
```php
/*
 * For all folders in the feature
 */
$Theamus->Call->set_folder_index("new-index");


/* 
 * For just the root folder of the feature 
 */
if (empty($folders)) $Theamus->Call->set_folder_index("new-index");
// > ACTUAL PATH: feature/views/new-index.php


/*
 * For a recursed folder in the feature (:://localhost/feature/folder1/folder2/)
 */
if (end($folders) == "folder2") $Theamus->Call->set_folder_index("new-index");
// > ACTUAL PATH: feature/views/folder1/folder2/new-index.php
```

##Notes:
 - If the file you specified doesn't exist, Theamus will default to a file called `index.php`. If still that file doesn't exist, you'll get a 404.