# Theme Layouts
Theme layouts are different versions of your theme that is callable by either features or pages. The point of these layouts are to provide a similar look and feel to different types of features across a website. As of right now there's no telling what's a popular feature that you should style for, so sometimes you might be styling a theme for a specific website rather than a generic theme that can accommodate all different types of popular features.

There are __4__ required layouts that must be present with every theme. The uses for these layouts are used by Theamus and not always used by features. Those 4 are:

1. default
1. error
1. blank
1. empty

## Default Layout
This layout will be exactly that. A default layout. Whenever a page is created, whenever a feature doesn't specify a layout, the default layout will be called for. Also, if there's ever a time when a feature is calling for a layout that isn't included in the theme, the default layout will be used instead.

## Error Layout
For error pages and all of your erroring needs.

## Blank Layout
This is a layout that strictly only outputs the content of the request. No JS, no CSS, no HTML. Just the content.

## Empty Layout
This is a layout that _does_ output the contents of the request along with the JS, CSS, and other HTML. Also, this layout should have no structure to it. To the user, it should only be a white space with text. The CSS and JS can make a difference, but by default there should be nothing on this page but plain content.

# Other Layouts
There's a couple layouts that Theamus uses in which you can include in your theme. Three, to be specific:

1. login
2. register
3. password-reset

To add these to your theme, in the config file, under the "layouts" key, add something like:

```json
"layouts": [
  {"layout":"default"}
  ...
  {
    "layout":"login",
    "file":"login-layout.php",
    "allow_nav": false
  },
  {
    "layout":"register",
    "file":"register-layout.php",
    "allow_nav": false
  },
  {
    "layout":"password-reset",
    "file":"pwreset-layout.php",
    "allow_nav": false
  }
]
```
