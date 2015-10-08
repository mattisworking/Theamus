# User Information
There's an easy way to get user information from the database using Theamus. It's not even a function!

## All Information

```php
print_r($Theamus->User->user);
Array
(
    [id] => 1
    [username] => user
    [password] => (hashed password)
    [email] => user@gmail.com
    [firstname] => John
    [lastname] => Doe
    [birthday] => 1991-01-01
    [gender] => m
    [admin] => 1
    [groups] => everyone,administrators
    [permanent] => 1
    [phone] => 555-555-1234
    [picture] => default-user-picture.png
    [created] => 2015-08-31 15:09:24
    [active] => 1
    [activation_code] => (hashed activation code)
)
```


## Specific Information
```php
echo $Theamus->User->user['firstname']." ".$Theamus->User->user['lastname'];
// John Doe
```

> If the user is not logged in, all of the values will be returned as `NULL`.
