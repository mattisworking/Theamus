# Theme Navigation
You're going to want navigation included in your theme. It's just the way things are. It's how people get around, man. But don't be silly and statically create navigation. Let Theamus take care of that! All you need to do is define "navigation areas" for the spots where you want navigation.

In order to create a "navigation area" all you need to do is add an array item to the `navigation` array in `config.json`. Let's say you added "footer" to that array. It would look something like this:

```json
"navigation": ["main", "footer"]
```

Main should always be on there. That's where all of the links will default go to. Footer is something extra, though.

## Including Main Navigation
```html
<ul class="nav">
  <?php echo $Theamus->Theme->get_page_navigation("main"); ?>
</ul>
```

The contents of the `get_page_navigation()` function are formatted in `<li>` elements, to make your code cleaner!

## Including Other Navigation
```php
<?php echo $Theamus->Theme->get_page_navigation("footer", "added-class"); ?>
```

Unlike the main navigation, this one comes fully wrapped in a `<ul>` element. The first variable is the name of the navigation area, the second is any class names that you would like to add to that element.

## Including Extra Navigation
You might remember the `allow_nav` key when defining your layout. That's what "extra" navigation is. This navigation is used by features, or by pages. Unlink "other navigation", this is _not_ defined through Theamus Navigation, but rather through developers (features) and users (pages).

If you decide to have extra navigation on your layout, you would include it with:
```php
<?php echo $Theamus->Theme->get_page_navigation("extra", "added-class"); ?>
```

It returns a complete `<ul>` element just like when you include other navigation. The only difference here is the keyword `extra`. That's important.

If you decide not to have navigation, and yet there's still extra navigation defined, it just won't show up. Nothing else will happen.
