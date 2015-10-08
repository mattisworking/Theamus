# Feature Configuration
aka `config.php`

This file holds a bunch of information about the Theamus Feature that you're building.  Unlike the `files.info.php` file, this file is much less used. It's still loaded on every call, but is more for Theamus to know things about the feature, rather than the specifics of a page request.

Also, unlike the `files.info.php` file, this file only holds a certain amount of information. It's also a file dedicated to a single function call.

## Sample

```php
<?php

$Theamus->Call->set_feature_config(array(
    // System Required
    "theamus_version" => "1.3.0",
    "permissions"     => array("database", "files"),

    // Feature Required
    "folder_name"     => "great_calc",
    "feature_name"    => "The Greatest Calculator Ever",
    "feature_version" => "0.0.0",
    "custom_folders"  => array(
        "php"        => "php",
        "javascript" => "js",
        "css"        => "css",
        "class"      => "php"
    ),

    // Feature Optional
    "load_files" => array(
        "api" => array(),
        "function" => array()
    ),
    "release_notes" => array(
        "0.0.0" => array("Currently in development.")
    ),
    "configuration_scripts" => array(
        "install" => array(""),
        "update" => array("")
    ),

    // Optional Author Information
    "author" => array(
        "name"    => array("developer name"),
        "alias"   => array("pwn_newbz11212"),
        "email"   => array("messwiththebest992@hotmail.com"),
        "company" => "Some Development Company Inc."
    )
));
```

## Explanation

The whole thing is a function call: `Theamus->Call->set_feature_config()`.  That accepts an array full of the information. The information is a key => value array.

|Key|Required?|Description|
| --- | :---:| --- |
|theamus_version|_optional_|The _oldest_ version of Theamus that your feature works on. _currently not used_|
|permissions|_optional_|The permissions that this feature would like to use. _currently not used_|
|folder_name|_required_|The name of the folder that the feature lives in|
|feature_name|_required_|The pretty name of the feature|
|feature_version|_required_|The current version of the feature|
|custom_folders|_required_|An array containing the names of folders as defined by you that hold different types of files|
|custom_folders.php|_required_|Where .php files live (class files or any script files)|
|custom_folders.javascript|_required_|Where javascript files live ($feature['js']['files'])|
|custom_folders.css|_required_|Where style sheets live ($feature['css']['files'])|
|custom_folders.class|_required_|Where class files live ($feature['class'] OR $Theamus->Call->load_class())|
|load_files|_optional_|An array of files to load before the `files.info.php` file loads up|
|load_files.api|_optional_|Array of API class files to load when an API AJAX call has been made. (defined from the ROOT of your feature)|
|load_files.funtion|_optional_|Array of function files to load before the `files.info.php` file has been loaded (defined from the ROOT of your feature)|
|release_notes|_optional_|key => value array of release notes, where the "key" is the version number, and the "value" is an array of the notes|
|configuration_scripts|_optional_|An array of scripts that will run at certain points in a feature's life|
|configuration_scripts.install|_optional_|An array of scripts that will run when the feature is being installed (from the ROOT of your feature|
|configuration_scripts.update|_optional_|An array of scripts that will run when the feature is being updated (from the ROOT of your feature|
|author|_optional_|An array of author information|
|author.name|_optional_|An array of all the developers names who worked on this feature|
|author.alias|_optional_|An array of all the developers usernames|
|author.email|_optional_|An array of all the developers email addresses|
|author.company|_optional_|The company that made the feature|

---

&nbsp;

[_forrest said it best_](https://www.youtube.com/watch?v=WJ_yQ02xwsM)