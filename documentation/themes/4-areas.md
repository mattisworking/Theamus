# Theme Areas
Theme areas are a thing to help you reduce repetition in your theme's code. The purpose of these is to take a block of HTML code or anything and put it in it's own file to be called and included on any theme layout that you would like it on.

For example, we have a header that is kind of a pain to add to every layout that we make. Fret not, that's what the area is for.

To define this area, in the `config.json` file, add `"header":"header.php"` to the `areas` object.

Our default layout looks like this:
```html
<html>
<head><!--header stuff --></head>
<body>
  <header>
    <div id="logo"><img src="..."></div>
    <div id="companyName"><?php echo $Theamus->settings['name']; ?></div>

    <nav>
      <?php echo $Theamus->Theme->get_page_navigation("main"); ?>
    </nav>
  </header>

  <!-- rest of body -->
</body>
</html>
```

We just want to get the header out of there and make it reusable instead of having to copy and paste on every layout we have.

So, we create the `header.php` file (same as defined in `config.json`) and throw the header code in there:
```html
<header>
  <div id="logo"><img src="..."></div>
  <div id="companyName"><?php echo $Theamus->settings['name']; ?></div>

  <nav>
    <?php echo $Theamus->Theme->get_page_navigation("main"); ?>
  </nav>
</header>
```

Now back to the default layout, we can remove the header code and just include the area.

```html
<html>
<head><!--header stuff --></head>
<body>
  <?php $Theamus->Theme->get_page_area("header"); ?>

  <!-- rest of body -->
</body>
</html>
```

All of the contents in the `header.php` file will be executed as if it were called by itself. So it's safe to put PHP in the area files as well.
