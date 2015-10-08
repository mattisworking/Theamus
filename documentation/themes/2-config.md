# Theme Configuration

In the only required file, `config.json`, you need to fill out some stuff to make your theme a theme for Theamus.

```json
config.json:

{
  "layouts": [
    {
      "layout":"default", 
      "file":"index.php",
      "allow_nav": true
    },
    {
      "layout":"error",
      "file":"error.php",
      "allow_nav": false
    },
    {
      "layout":"blank",
      "file":"blank.php",
      "allow_nav": false
    },
    {
      "layout":"empty",
      "file":"empty.php",
      "allow_nav": false
    }
  ],
  "areas": {
    "header":"header.php",
    "body":"body.php",
  },
  "navigation": ["main"],
  "settings": false,
  "theme": {
    "folder":"theme-folder",
    "name":"Theamus Theme",
    "version":"1.0"
  },
  "author": {
    "name":"John Doe",
    "alias":"username",
    "company":"The Company, Co.",
    "email":"user@gmail.com"
  }
}
```

|Key|Description|
|---|---|
|layouts|An array of objects that define all of the different theme layouts that this theme has to offer. Some of these layouts are required. You have the freedom to add as many as you want, though. Features can call layouts, as well as pages (when creating/editing a page you can define the layout it will show on)|
|layouts.layout|The name of the layout (what the feature will call for when there is a need for it)|
|layouts.file|The file name that holds the contents of the layout, these are all up to you to define.|
|layouts.allow_nav|Whether or not the layout supports extra navigation from the Theamus system (used when creating/editing pages)|
|areas|Select areas of a layout that might be used multiple times on different layouts. The point of areas is to reduce repetition. This is structured like "area name":"area filename"|
|navigation|An array of all the potential spots for navigation in the theme. These options will show up when a user creates a link and wants to specify where the link should live.|
|settings|Don't worry about this, but it's required. Just set it to false.|
|theme|Theme information for Theamus!|
|theme.folder|The folder Theamus should look to for the theme contents|
|theme.name|A pretty name for the theme, this is what administrators will see.|
|theme.version|Version of the theme|
|author|Theme author information|
|author.name|The name of the author|
|author.alias|A username of the author|
|author.company|The company the author works for|
|author.email|A contact email address for the author|
