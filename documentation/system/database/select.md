# Database Select Function

```php
<?php
$Theamus->DB->select_from_table($table, $colums, $conditions, $extra);
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

### Samples
```php
<?php

$Theamus->DB->select_from_table(
  $Theamus->DB->table("users")); // selects everything from the table
```

```php
<?php

$Theamus->DB->select_from_table(
  $Theamus->DB->table("users"),
  array("username", "password")); // columns to return
```

```php
<?php

$Theamus->DB->select_from_table(
  $Theamus->DB->table("users"),
  array("id"), // column to return
  array("operator" => "AND",
      "conditions" => array("username" => "mary", "password" => "kitty101"))); // WHERE
```

```php
<?php

$Theamus->DB->select_from_table(
  $Theamus->DB->table("users"), // select from users table
  array("id"), // select id from table
  array(), // no conditions
  "LIMIT 5") // limit 5
```
