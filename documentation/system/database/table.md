# Database Table Names

Because Theamus uses prefixes on all of the table names, and because they are all randomly generated, there should be a way for you to call the table related to your feature easily. There is!

## Getting a Feature Table

```php
<?php

$Theamus->DB->table("files");
// returns t8us_files where "t8us" is the randomly generated prefix associated to your feature
```

## Getting a System Table

```php
<?php

$Theamus->DB->system_table("users");
// returns tm_users where "tm" is the system table prefix
```
