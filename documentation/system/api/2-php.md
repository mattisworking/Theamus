# Theamus API PHP
This is the backend of the API call. When you define the API `method` object key, this is what you'll be calling.

## Setting Up API
In your feature's `config.php` file, you'll find an option to define `load_files['api']`. That's where you'll say that you want to load a class file when an API call is being made to your feature.

```php
<?php
$Theamus->Call->set_feature_config(array(
    ...
    'load_files' => array(
        'api' => array('php/blog.class.php')
    ),
    ...
));
```

So now, the file `php/blog.class.php` will load up whenever an API call is made to the Blog feature.

## The API Class
Just a regular class, but in order to use Theamus, you'll need to pass it in the constructor function.

You'll notice that the name of the class is the same as the first array value when you're defining the method in the JavaScript options.

`php/blog.class.php`
```php
<?php
class Blog {
  // Let this class use Theamus
  public function __construct($t) { $this->Theamus = $t; }

  // Creates a post
  public function create_post($args) {
    return $args;
  }
}
```
