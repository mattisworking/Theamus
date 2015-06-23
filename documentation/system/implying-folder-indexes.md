Implying Folder Index Files
===

Implying index files is super useful in a few ways, but mainly it allows you to hide the name of your folder index file to make the URL more direct and readable.

Back in the day, your URL would be forced to something like this:

```text
http://localhost/feature/index/variable1/variable2
```

But now, you can make it look better: 
```text
http://localhost/feature/variable1/variable2
```

Theamus automatically recognizes everything for you. If you have implied folders for the one being requested, then it will check to see if `variable1` is an existing file. If it is, then it will go to that file (as it should) but if it's not a file, instead of a 404 it will set variable1 as a variable from the URL.

>__Note__:
>The order of implying and setting indexes is important! If you don't set a custom index _before_ you imply the index, it will assume the index file is `index.php`!

---

##Usage

Here's the basic syntax on how to imply folder indexes:
```php
$Theamus->Call->imply_folder_index( $folder_name );
```
---

##Example

For an example, we will have a __blog__ feature and the index page should read a page.
```text
://localhost/blog/read/this-is-a-post-title
```

But we don't want `read` to be in there, because we all know that's what your doing. Firstly, we need to set `read` as the default index file. That will make it so whenever you go to `blog/` it automatically loads the `read.php` view file.


```php
<files.info.php>
<?php

/* Make it so Theamus loads read.php when the Blog feature is called without any specified file */
$Theamus->Call->set_folder_index("read");

/* Now, assume that if a file doesn't exist, it's really meant to be a variable */
$Theamus->Call->imply_folder_index("root");

/** 
 * For clarity, because of these two function calls, the file path will look like
 * this when going to ://localhost/blog/ -> blog/views/read.php
 */
```

Let's say a user goes to `http://localhost/blog/this-is-a-post-title`. How do you access the post title? Easy!
```php
$Theamus->Call->parameters;

> array(
    [0] => "this-is-a-post-title"
  )
```
Then you can go from there!