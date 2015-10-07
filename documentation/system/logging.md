# Logging in Theamus
Logging is there for you to keep track of how your website is running. Whether there is any errors displaying, or if there's just logging for progression through a script.

## Enabling Logging
You can turn on logging via the administration panel when you follow these steps:

1. Login as an administrator to your website
1. On the top left (or right) of the screen, click on the hamburger menu (three horizontal lines)
1. Click on "Settings"
1. On the tabs of that window, click on "Settings"
1. Under the header "Developer Options" you'll see a multi-select box of different "Log Categories"

Anything that is selected in that box will be available to log.

> __Note:__ "System" log is always turned on.

## Logging Types

|Type|Description|
|---|---|
|System|System related logs, that come from system related functions like logging in, page creation/editing, or feature/theme installation/updating|
|Query|Any database query related logging|
|Developer|Any logging for developers|
|General|Logging just for the sake of logging. Can be just about anything|

## Viewing Logs
To view your logs, follow these steps:

1. Login as an administrator to your website
1. On the top left (or right) of the screen, click on the hamburger menu (three horizontal lines)
1. Click on "Settings"
1. On the top right of the "Settings" window, you'll see a menu item named "More", hover over that and click "View Logs".

## Logging
On to the fun, actually logging. It's simple.

### Query
```php
<?php
// Logs the last query error
$Theamus->Log->query($Theamus->DB->get_last_error());
```

### Developer
```php
<?php
$Theamus->Log->developer("Failed probably because of file permissions?");
```

### General
```php
<?php
$Theamus->Log->general("File upload completed at ".time());
```
