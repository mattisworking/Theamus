# Cheat Sheet
Here you will find an assortment of all the function calls you can make within a theme and what their purpose is.

### Theme Variables
```php
<?php echo $Theamus->Theme->get_page_variable("variable name"); ?>
```
That will print out the a variable value.

|Variable Name|Description|
|---|---|
|title|The title of the page that will show up in the browser's title bar.|
|header|Header of the page that will show up on layouts|
|theme_path|A URL path to the theme's root folder. This is used for including theme-specific resources without having to statically define the theme path while not worrying about relativity.|
|site_name|The name of the website. AKA `Theamus->settings['name']`|
|error_type|The number associated to the error that happened. Mostly used for error pages.|
|js|A collection of all the Theamus-related JavaScript files that need to be included on every page call|
|css|A collection of all the Theamus-related stylesheets that need to be included on every page call|
|base|Defintion of the `<base>` tag for the header.|
|has_admin|0 or 1 value of whether or not the user has access to the administration panel|
|favicon|The favicon path for the website|

#### Examples:
```php
<?php
$Theamus->Theme->get_page_variable("title");
// returns "My Cat's Website!" OR "Blog - My Cat's Website!"

$Theamus->Theme->get_page_variable("header");
// returns "Blog Homepage" OR nothing if it isn't set.

$Theamus->Theme->get_page_variable("theme_path");
// returns "http(s)://localhost/theamus/themes/theme-folder/"

$Theamus->Theme->get_page_variable("site_name");
// returns "My Cat's Website!"

$Theamus->Theme->get_page_variable("error_type");
// returns 404 if 404 error. etc etc. 0 is default.

$Theamus->Theme->get_page_variable("js");
// returns a string of <script src=""> elements to be included on a layout

$Theamus->Theme->get_page_variable("css");
// returns a string of <link href=""> elements to be included on a layout

$Theamus->Theme->get_page_variable("base");
// returns <base href="http(s)://localhost/theamus/"> THIS IS REQUIRED ON EVERY LAYOUT EXCEPT BLANK

$Theamus->Theme->get_page_variable("has_admin");
// returns 0 if the user is not an administrator - 1 if he/she/it is

$Theamus->Theme->get_page_variable("favicon");
// returns "media/img/favicon.ico" or the path to the defined favicon

?>
```

## Sample Default Layout

```html
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php echo $Theamus->Theme->get_page_variable("base"); ?>
    <link rel="icon" href="<?php echo $Theamus->Theme->get_page_variable('favicon'); ?>">
    <title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
    <?php echo $Theamus->Theme->get_page_variable("css"); ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $Theamus->Theme->get_page_variable('theme_path'); ?>css/default.css" />
  </head>
  <body>
    <?php $Theamus->Theme->get_page_area("admin"); ?>

    <?php $Theamus->Theme->content(); ?>

    <?php echo $Theamus->Theme->get_page_variable("js"); ?>
  </body>
</html>
```

> Take note of the `get_page_area("admin")` function call. This will include the administration panel with the page load. If you want to have the administration panel be accessible, then you _need_ to include that call. There is no other way to access it.

## Sample Blank Layout
```php
<?php $Theamus->Theme->blank_content();
```

> Blank content. That line of code is the whole file.

## Sample Empty Layout  
```html
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php echo $Theamus->Theme->get_page_variable("base"); ?>
    <title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
    <?php echo $Theamus->Theme->get_page_variable("css"); ?>
    <link rel="stylesheet" href="<?php echo $Theamus->Theme->get_page_variable('theme_path'); ?>/css/main.css" />
    <link rel="icon" href="<?php echo $Theamus->Theme->get_page_variable('favicon'); ?>">
  </head>
  <body>
    <?php echo $Theamus->Theme->content(); ?>
    <?php echo $Theamus->Theme->get_page_variable("js"); ?>
  </body>
</html>
```

## Sample Error Layout
```html
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php echo $Theamus->Theme->get_page_variable("base"); ?>
    <title><?php echo $Theamus->Theme->get_page_variable("title"); ?></title>
    <?php echo $Theamus->Theme->get_page_variable("css"); ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $Theamus->Theme->get_page_variable('theme_path'); ?>/css/error.css" />
    <link rel="icon" href="<?php echo $Theamus->Theme->get_page_variable('favicon'); ?>">
  </head>
  <body>
    Error! <?php echo $Theamus->Theme->get_page_variable("error_type"); ?>
    <?php echo $Theamus->Theme->get_page_variable("js"); ?>
  </body>
</html>
```

> To get the error type to show on the page, you get the page variable "error_type". It will return the error code. (e.g. 404, 500)
