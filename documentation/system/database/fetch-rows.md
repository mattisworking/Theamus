# Database Fetch Rows
What good is having query functions if you can't get the results?!

```php
<?php

$Theamus->DB->fetch_rows($query);
```

## Example
Here's your table "users":
```
|id| username | password |
|--|----------|----------|
| 1| john     | pa$$word |
| 2| mary     | kitty101 |
```

## Samples

### Multiple Returns
```php
<?php

$query = $Theamus->DB->select_from_table($Theamus->DB->table("users"));
$Theamus->DB->fetch_rows($query);

/* returns
Array(
  0 => Array(
    "id" => 1,
    "username" => "john",
    "password" => "pa$$word"
  )
  1 => Array(
    "id" => 2,
    "username" => "mary",
    "password" => "kitty101"
  )
)
*/
```


### Single Return
```php
<?php

$query = $Theamus->DB->select_from_table(
  $Theamus->DB->table("users"),
  array("operator" => "", "conditions" => array("id" => 1));
)
$Theamus->DB->fetch_rows($query);

/* returns
Array(
  "id" => 1
  "username" => "john",
  "password" => "pa$$word"
)
*/
```
