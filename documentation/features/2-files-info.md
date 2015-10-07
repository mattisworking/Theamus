# Feature Files Information
aka `files.info.php`

This file's purpose is to handle the requests based on the file called. That way, you can specify things specifically instead of generally. For example, you have a file `great_calc/views/scientific.php`. You wouldn't want the title to say "Calculator" like it does when the index file is called. You want it to say "Scientific Calculator" instead. That's the purpose of the `files.info.php` file.

# Basics
The file is PHP. Pure PHP. Here's what a basic file would look like:

```php
<?php

switch ($Theamus->Call->get_called_file()) {
    case "index.php":
        $feature['title'] = $feature['header'] = "Calculator";
        break;
}
```

All this does is say "when the index.php file is called, set the title of the page and the header of the page to 'Calculator'." Easy as that.

---

# Cheatsheet
Obviously there's more you can do here than just set the title of the page. You can define the theme template, check for user permissions, and add javascript files or style sheets on a file per file basis.

## Feature Keys
By keys, I mean `$feature[key]`. This is everything that can be defined to the feature:

|Key|Description|
| --- | ---|
|title|Sets the title of the browser|
|header|Sets the header of the page, if the theme allows it.|
|js|An array of script files to load when the page loads. Add to it using `$feature['js']['file'][] = "yourfile"`|
|css|An array of style sheets to load when the page loads. Add to it using `$feature['css']['file'][] = "yourfile"`.|
|theme|Requests a specific theme layout from the theme. If it doesn't exist, the theme will revert to the default layout.|
|nav|A key => value array of links to add to the theme. Also known as "extra navigation" to the theme|
|class.file|The file name of a class that you want to include at page load|
|class.init|The class name of the file that was loaded with `$feature['class']['file']`|
|json|Boolean to set the header to return JSON only results. No theme, js files or stylesheets will be loaded if TRUE|

### $feature['nav']
This navigation is used based on the theme. You can define it to provide a feature, or file, specific navigation on the theme.

The structure is a key => value array, but can be recursive. Here's an example:

```php
<?php

$feature['nav'] = array(
    "Home" => "great_calc/",
    "Other Calculators" => array(
        "path" => "great_calc/", // This is the path to the "Other Calculators" link
        "Scientific" => "great_calc/scientific/",
        "Programmer" => "great_calc/programmer/"
    )
);
```

To explain, the "key" is the link text, where the "value" is the link path. When you go into recursion, you need to define a "path" key/value for the link text that opens the recursion.

### Folders
Sometimes, you'll want to check user's access to a complete folder to avoid repeating yourself for each file. That's understandable which is why you get the `$folders` variable with every call. If Theamus detects the file that was loaded exists in a folder, it will throw it in the `$folders` variable as an array item.

Let's say a user navigates their way to `theamus/great_calc/calculators/scientific/`. That path is actually `theamus/features/great_calc/views/calculator/scientific.php` making the folder "calculator" and the file "scientific.php". This is recursive, so whenever there's more than one folder, all of them will show up in the `$folders` array.

```php
<?php

print_r($folders);

Array (
    [0] => "calculator"
)
```

### Getting the Requested File
You might have seen above how to do it, but here it is again.

If your user is going to `theamus/great_calc/index.php` and you want to find out the file that was requested, use the function `$Theamus->Call->get_called_file()`. That will simply return `index.php`, or the file requested.

### Checking a user's permission
See `documentation/system/user-permissions.md` for more information about actually checking their permissions.

```php
<?php

// If the user isn't a calculator administrator, and they're trying to get to the administrator folder... 
if (end($folders) === "admin" && !$Theamus->User->in_group("calculator_administrators")) {
    $Theamus->go_back(); // Send them back, or up one level
}

---

switch ($Theamus->Call->get_called_file()) {
    case "index.php":
        ...
    
    case "scientific.php":
        // If the user doesn't have permission to access the scientific calculator... send them back/up one level
        if (!$Theamus->User->has_permission("scientific_calculator")) $Theamus->go_back();
        ....
        break;
}
```

### Defining Classes and their Files
There's two ways to do this. The old way and the new way.

#### The old way
This way, you can only load in one class per call. That's pretty silly, but if you need to it's here. (It's a legacy thing.)

```php
<?php

$feature['class']['file'] = "someclass.class.php";
$feature['class']['init'] = "SomeClass";
```
Then, in any of your view files, you now have access to use the `$SomeClass` variable, which is started by Theamus during page load.


#### The new way
This way is __recommended__ and allows you to load more than one class during page load.

```php
<?php

$Theamus->Call->load_class("someclass.class.php", "SomeClass");
```
Now, in any of your view files, you have access to use the `SomeClass` variable, which is started by Theamus during page load.


### Feature Folders
For a few of the `$feature` keys, the folders are defined in the `config.php` file. The keys where that's true are `js`, `css`, and `class`. The point of doing this was to allow you, as a developer, a way to define folders however you wanted to, and not have to repeat yourself when including files.

So when you want to load any of those files, you can just set the value to be the name of the file. In `config.php` you will set the folder of the file, and Theamus will stitch everything together.

---

# Setting Custom Folder Index Files

In the feature's `files.info.php` file, you can customize the name of the index file by the one simple function call of `$Theamus->Call->set_folder_index( $file_name )`.

Here's a few examples:
```php
// For all folders in the feature
$Theamus->Call->set_folder_index("new-index");


// For just the root folder of the feature 
if (empty($folders)) $Theamus->Call->set_folder_index("new-index");
// > ACTUAL PATH: feature/views/new-index.php


// For a recursed folder in the feature (:://localhost/feature/folder1/folder2/)
if (end($folders) == "folder2") $Theamus->Call->set_folder_index("new-index");
// > ACTUAL PATH: feature/views/folder1/folder2/new-index.php
```

## Notes:
 - If the file you specified doesn't exist, Theamus will default to a file called `index.php`. If still that file doesn't exist, you'll get a 404.

---

# Implying Folder Index Files

Implying index files is super useful in a few ways, but mainly it allows you to hide the name of your folder index file to make the URL more direct and readable.

Back in the day, your URL would be forced to something like this:

```text
://localhost/feature/index/variable1/variable2
```

But now, you can make it look better: 
```text
://localhost/feature/variable1/variable2
```

Theamus automatically recognizes everything for you. If you have implied folders for the one being requested, then it will check to see if `variable1` is an existing file. If it is, then it will go to that file (as it should) but if it's not a file, instead of a 404 it will set variable1 as a variable from the URL.

>__Note__:
>The order of implying and setting indexes is important! If you don't set a custom index _before_ you imply the index, it will assume the index file is `index.php`!

## Usage

Here's the basic syntax on how to imply folder indexes:
```php
$Theamus->Call->imply_folder_index( $folder_name );
```

## Example

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
<?php

print_r($Theamus->Call->parameters);

Array (
    [0] => "this-is-a-post-title"
)
```
Then you can go from there!

---

&nbsp;

[_forrest said it best_](https://www.youtube.com/watch?v=Otm4RusESNU)