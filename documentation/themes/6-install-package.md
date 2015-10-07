# Packaging Themes for Installation
All you need to know is that everything needs to live in the root of the zip file.

## DO
```
theme.zip
  ...
|-config.json
```

## DO NOT
```
theme.zip
|-theme-folder
   ...
 |-config.json
```

There's nothing more to it. Just the contents of your theme in the root of a zip folder.
