# Database Errors

```php
<?php
$Theamus->DB->get_last_error();
```

Returns the latest error

## Example
```php
<?php
$Theamus->DB->select_from_table("some_table");
$Theamus->DB->get_last_error();

// returns "Table 'some_table' doesn't exist"
```
