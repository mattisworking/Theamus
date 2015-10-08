# Database Insert Function

```php
<?php
$Theamus->DB->insert_table_row($table, $data);
```

This function returns a PDO or mysqli object.

## Example
Here's your table "users":
```
|id| username | password |
```

## Single Insert Sample
```php
<?php

$Theamus->DB->insert_table_row(
  $Theamus->DB->table("users"), // insert into users table
  array("username" => "john",
    "password" => "pa$$word"));
```

## Multiple Insert Sample
```php
<?php

$Theamus->DB->insert_table_row(
  $Theamus->DB->table("users"),
  array(
    array("username" => "john",
      "password" => "pa$$word"),
    array("username" => "mary",
      "password" => "kitty101")
  ));
```
