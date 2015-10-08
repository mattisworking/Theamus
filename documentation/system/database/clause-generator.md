# Database Clause Generator
WHERE statements can be tricky, and this generator can be tricky too. The purpose of this is to give security to your query clause, without adding all of the code to sanitize and clean it up for database input.

## Basic Example
```php
<?php

array(
  "operator" => "" // can be AND, OR, &&, ||
  "conditions" => array("column" => "value")
);

// WHERE (`column` = value)
```

## Intermediate Example
```php
<?php

array(
  "operator" => "AND",
  "conditions" => array("username" => "john", "activated" => 1)
);

// WHERE (`username` = "john" AND `activated` = 1)
```

## Advanced Example (with recursion)
```php
<?php

array(
  "operator" => "OR",
  "conditions" => array(
    array("operator" => "AND", "conditions" => array("username" => "john", "activated" => 1)),
    array("operator" => "AND", "conditions" => array("username" => "mary", "activated" => 1))
  )
);

// WHERE ((`username` = "john" AND `activated` = 1) OR (`username` = "mary" AND `activated` = 1))
```

## Example with Functions
```php
<?php

array(
  "operator" => "",
  "conditions" => array("[func]creation_date" => "now()")
);

// WHERE `creation_date` = now()
```

## Example with different condition operators
```php

<?php

array(
  "operator" => "",
  "conditions" => array("[%]username" => "asd%")
);

// WHERE `username` LIKE "asd%"

array(
  "operator" => "",
  "conditions" => array("[!%]username" => "asd%")
)

// WHERE `username` NOT LIKE "asd%"
```
### All Conditional Operators
Add any of these to brackets before the column name (key) in the conditions array

|Operator|Result|
|---|---|
|!|\`column` != value|
|`|column = value|
|%|\`column` LIKE value|
|!%|\`column` NOT LIKE value|
|<|\`column` < value|
|>|\`column` > value|
|<=|\`column` <= value|
|>=|\`column` >= value|
