# Database Update Function

```php
<?php
$Theamus->DB->update_table_row($table, $data, $conditions);
```

This function returns a PDO or mysqli object.

## Example
Here's your table "users":
```
|id| username | password |
|--|----------|----------|
| 1| john     | pa$$word |
| 2| mary     | kitty101 |
```

## Single Update Sample
```php
<?php

$Theamus->DB->update_table_row(
  $Theamus->DB->table("users"),
  array("password" => "john!!"),
  array("operator" => "",
    "conditions" => array("id" => 1)));
```

## Multiple Update Sample
```php
<?php

$Theamus->DB->update_table_row(
  $Theamus->DB->table("users"),
  array(
    array("password" => "john!!"),
    array("password" => "mary!!")
  ),
  array(
    array("operator" => "", "conditions" => array("id" => 1)),
    array("operator" => "", "conditions" => array("id" => 2))
  )
);
```
