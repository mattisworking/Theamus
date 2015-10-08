# Database Count Rows

```php
<?php
$Theamus->DB->count_rows($query);
```

## Example
Here's your table "users":
```
|id| username | password |
|--|----------|----------|
| 1| john     | pa$$word |
| 2| mary     | kitty101 |
```

### Sample
```php
<?php

$query = $Theamus->DB->select_from_table($Theamus->DB->table("users"));
$Theamus->DB->count_rows($query);

// returns 2
```
