# Database Delete Function

```php
<?php
$Theamus->DB->delete_table_row($table, $conditions);
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

## Single Delete Sample
```php
<?php

$Theamus->DB->delete_table_row(
  $Theamus->DB->table("users"),
  array("operator" => "",
    "conditions" => array("id" => 1)));
```

## Multiple Delete Sample
```php
<?php

$Theamus->DB->delete_table_row(
  $Theamus->DB->table("users"),
  array(
    array("operator" => "", "conditions" => array("id" => 1)),
    array("operator" => "", "conditions" => array("id" => 2))
  )
);
```
